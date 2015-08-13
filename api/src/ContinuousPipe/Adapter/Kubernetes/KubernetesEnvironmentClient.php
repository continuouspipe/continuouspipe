<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\EnvironmentClient;
use ContinuousPipe\Adapter\Kubernetes\Transformer\EnvironmentTransformer;
use ContinuousPipe\Model\Environment;
use Kubernetes\Client\Client;
use Kubernetes\Client\Exception\NamespaceNotFound;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\NamespaceClient;
use Kubernetes\Client\Repository\ObjectRepository;
use Kubernetes\Client\Repository\WrappedObjectRepository;
use LogStream\Logger;
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
     * @param Client                 $client
     * @param EnvironmentTransformer $environmentTransformer
     */
    public function __construct(Client $client, EnvironmentTransformer $environmentTransformer)
    {
        $this->client = $client;
        $this->environmentTransformer = $environmentTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(Environment $environment, Logger $logger)
    {
        $namespace = $this->getOrCreateNamespace($environment, $logger);
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
     * @param Environment $environment
     * @param Logger      $logger
     *
     * @return KubernetesNamespace
     */
    private function getOrCreateNamespace(Environment $environment, Logger $logger)
    {
        $namespaceRepository = $this->client->getNamespaceRepository();
        $namespaceName = $environment->getIdentifier();

        try {
            $namespace = $namespaceRepository->findOneByName($namespaceName);
            $logger->append(new Text('Reusing existing namespace "%s"', $namespaceName));
        } catch (NamespaceNotFound $e) {
            $namespace = new KubernetesNamespace(new ObjectMetadata($environment->getIdentifier()));
            $namespace = $this->client->getNamespaceRepository()->create($namespace);
            $logger->append(new Text('Created new namespace "%s"', $namespaceName));
        }

        return $namespace;
    }

    private function getObjectTypeAndName(KubernetesObject $object)
    {
        $objectClass = get_class($object);
        $type = substr($objectClass, strrpos($objectClass, '/'));

        return sprintf('%s "%s"', $type, $object->getMetadata()->getName());
    }
}
