<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\EnvironmentClient;
use ContinuousPipe\Adapter\Kubernetes\Transformer\EnvironmentTransformer;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Client;
use Kubernetes\Client\Exception\NamespaceNotFound;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServiceSpecification;
use Kubernetes\Client\NamespaceClient;
use Kubernetes\Client\Repository\ObjectRepository;
use Kubernetes\Client\Repository\WrappedObjectRepository;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

class KubernetesEnvironmentClient implements EnvironmentClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var EnvironmentTransformer
     */
    private $environmentTransformer;
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param Client $client
     * @param EnvironmentTransformer $environmentTransformer
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(Client $client, EnvironmentTransformer $environmentTransformer, LoggerFactory $loggerFactory)
    {
        $this->client = $client;
        $this->environmentTransformer = $environmentTransformer;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(Environment $environment, DeploymentContext $deploymentContext)
    {
        $logger = $deploymentContext->getLogger();

        $namespace = $this->getOrCreateNamespace($environment, $deploymentContext);
        $namespaceObjects = $this->environmentTransformer->getElementListFromEnvironment($environment);
        $namespaceClient = $this->client->getNamespaceClient($namespace);

        foreach ($namespaceObjects as $object) {
            $objectRepository = $this->getObjectRepository($namespaceClient, $object);
            $objectName = $object->getMetadata()->getName();

            if ($objectRepository->exists($objectName)) {
                $logger->append(new Text('Updating '.$this->getObjectTypeAndName($object)));
                $objectRepository->update($object);
            } else {
                $logger->append(new Text('Creating '.$this->getObjectTypeAndName($object)));
                $objectRepository->create($object);
            }
        }

        $this->populateEnvironmentPublicEndpoints($namespaceClient, $namespaceObjects, $deploymentContext);

        return $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $namespaces = $this->client->getNamespaceRepository()->findAll();
        $environments = [];

        foreach ($namespaces->getNamespaces() as $namespace) {
            $namespaceMetadata = $namespace->getMetadata();

            $environments[] = new Environment($namespaceMetadata->getName(), $namespaceMetadata->getName());
        }

        return $environments;
    }

    /**
     * Create the namespace object.
     *
     * @param NamespaceClient  $namespaceClient
     * @param KubernetesObject $object
     *
     * @return ObjectRepository
     */
    private function getObjectRepository(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        if ($object instanceof Pod) {
            $repository = $namespaceClient->getPodRepository();
        } elseif ($object instanceof Service) {
            $repository = $namespaceClient->getServiceRepository();
        } elseif ($object instanceof ReplicationController) {
            $repository = $namespaceClient->getReplicationControllerRepository();
        } else {
            throw new \RuntimeException(sprintf(
                'Unsupported object of type "%s"',
                get_class($object)
            ));
        }

        return new WrappedObjectRepository($repository);
    }

    /**
     * Get or create namespace for the given environment.
     *
     * @param Environment       $environment
     * @param DeploymentContext $deploymentContext
     *
     * @return KubernetesNamespace
     */
    private function getOrCreateNamespace(Environment $environment, DeploymentContext $deploymentContext)
    {
        $logger = $deploymentContext->getLogger();

        $namespaceRepository = $this->client->getNamespaceRepository();
        $namespaceName = $environment->getIdentifier();

        try {
            $namespace = $namespaceRepository->findOneByName($namespaceName);
            $logger->append(new Text(sprintf('Reusing existing namespace "%s"', $namespaceName)));
        } catch (NamespaceNotFound $e) {
            $namespace = new KubernetesNamespace(new ObjectMetadata($environment->getIdentifier()));
            $namespace = $this->client->getNamespaceRepository()->create($namespace);
            $logger->append(new Text(sprintf('Created new namespace "%s"', $namespaceName)));
        }

        return $namespace;
    }

    /**
     * @param KubernetesObject $object
     * @return string
     */
    private function getObjectTypeAndName(KubernetesObject $object)
    {
        $objectClass = get_class($object);
        $type = substr($objectClass, strrpos($objectClass, '/'));

        return sprintf('%s "%s"', $type, $object->getMetadata()->getName());
    }

    /**
     * @param NamespaceClient $namespaceClient
     * @param KubernetesObject[] $namespaceObjects
     * @param DeploymentContext $deploymentContext
     */
    private function populateEnvironmentPublicEndpoints(NamespaceClient $namespaceClient, array $namespaceObjects, DeploymentContext $deploymentContext)
    {
        $endpoints = [];

        foreach ($namespaceObjects as $object) {
            if ($this->isAPublicObject($object)) {
                $log = $deploymentContext->getLogger()->append(new Text('Waiting public endpoint of '.$this->getObjectTypeAndName($object)));
                $logger = $this->loggerFactory->from($log);

                try {
                    $logger->start();

                    $endpoint = $this->waitServicePublicEndpoint($namespaceClient, $object, $logger);
                    $endpoints[] = $endpoint;

                    $logger->append(new Text(sprintf('Found public endpoint "%s": %s', $endpoint->getName(), $endpoint->getAddress())));
                    $logger->success();
                } catch (\Exception $e) {
                    $logger->append(new Text($e->getMessage()));
                    $logger->failure();
                }
            }
        }

        $deploymentContext->getDeployment()->setPublicEndpoints($endpoints);
    }

    /**
     * @param KubernetesObject $object
     * @return bool
     */
    private function isAPublicObject(KubernetesObject $object)
    {
        return $object instanceof Service && $object->getSpecification()->getType() == ServiceSpecification::TYPE_LOAD_BALANCER;
    }

    /**
     * @param NamespaceClient $namespaceClient
     * @param Service $service
     * @param Logger $logger
     * @return PublicEndpoint
     * @throws \Exception
     */
    private function waitServicePublicEndpoint(NamespaceClient $namespaceClient, Service $service, Logger $logger)
    {
        $serviceName = $service->getMetadata()->getName();

        $attempts = 0;
        do {
            try {
                return $this->getServicePublicEndpoint($namespaceClient, $serviceName);
            } catch (\Exception $e) {
                $logger->append(new Text($e->getMessage()));
            }

            sleep(5);
        } while (++$attempts < 10);

        throw new \Exception('Attempted too many times.');
    }

    /**
     * @param NamespaceClient $namespaceClient
     * @param string $serviceName
     * @return PublicEndpoint
     * @throws \Exception
     */
    private function getServicePublicEndpoint(NamespaceClient $namespaceClient, $serviceName)
    {
        $foundService = $namespaceClient->getServiceRepository()->findOneByName($serviceName);

        if ($status = $foundService->getStatus()) {
            if ($loadBalancer = $status->getLoadBalancer()) {
                $ingresses = $loadBalancer->getIngresses();

                if (count($ingresses) > 0) {
                    $ingress = current($ingresses);
                    $ip = $ingress->getIp();

                    if (!empty($ip)) {
                        return new PublicEndpoint($serviceName, $ip);
                    } else {
                        throw new \Exception('Empty IP found');
                    }
                } else {
                    throw new \Exception('No ingress found');
                }
            } else {
                throw new \Exception('No load balancer found');
            }
        }

        throw new \Exception('No status found');
    }
}
