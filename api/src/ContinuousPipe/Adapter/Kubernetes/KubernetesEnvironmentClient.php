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
    public function createOrUpdate(Environment $environment)
    {
        $namespace = $this->getOrCreateNamespace($environment);
        $namespaceObjects = $this->environmentTransformer->getElementListFromEnvironment($environment);
        $namespaceClient = $this->client->getNamespaceClient($namespace);

        foreach ($namespaceObjects as $object) {
            $objectRepository = $this->getObjectRepository($namespaceClient, $object);
            $objectName = $object->getMetadata()->getName();

            if ($objectRepository->exists($objectName)) {
                $objectRepository->update($object);
            } else {
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
     *
     * @return KubernetesNamespace
     */
    private function getOrCreateNamespace(Environment $environment)
    {
        $namespaceRepository = $this->client->getNamespaceRepository();
        $namespaceName = $environment->getIdentifier();

        try {
            $namespace = $namespaceRepository->findOneByName($namespaceName);
        } catch (NamespaceNotFound $e) {
            $namespace = new KubernetesNamespace(new ObjectMetadata($environment->getIdentifier()));
            $namespace = $this->client->getNamespaceRepository()->create($namespace);
        }

        return $namespace;
    }
}
