<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\EnvironmentClient;
use ContinuousPipe\Adapter\EnvironmentNotFound;
use ContinuousPipe\Adapter\Kubernetes\Transformer\ComponentTransformer;
use ContinuousPipe\Model\Environment;
use Kubernetes\Client\Client;
use Kubernetes\Client\Exception\NamespaceNotFound;
use Kubernetes\Client\Model\KubernetesNamespace;

class KubernetesEnvironmentClient implements EnvironmentClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var ComponentTransformer
     */
    private $componentTransformer;

    /**
     * @param Client $client
     * @param ComponentTransformer $componentTransformer
     */
    public function __construct(Client $client, ComponentTransformer $componentTransformer)
    {
        $this->client = $client;
        $this->componentTransformer = $componentTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $namespaces = $this->client->getNamespaceRepository()->findAll();
        $environments = [];

        foreach ($namespaces->getNamespaces() as $namespace) {
            $environments[] = $this->namespaceToEnvironment($namespace);
        }

        return $environments;
    }

    /**
     * {@inheritdoc}
     */
    public function find($identifier)
    {
        try {
            $namespace = $this->client->getNamespaceRepository()->findOneByName($identifier);
        } catch (NamespaceNotFound $e) {
            throw new EnvironmentNotFound();
        }

        return $this->namespaceToEnvironment($namespace);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Environment $environment)
    {
        $namespaceRepository = $this->client->getNamespaceRepository();
        $namespaceRepository->delete(
            $namespaceRepository->findOneByName($environment->getIdentifier())
        );
    }

    /**
     * @param KubernetesNamespace $namespace
     *
     * @return Environment
     */
    private function namespaceToEnvironment(KubernetesNamespace $namespace)
    {
        $namespaceMetadata = $namespace->getMetadata();
        $namespaceClient = $this->client->getNamespaceClient($namespace);
        $objects = array_merge(
            $namespaceClient->getServiceRepository()->findAll()->getServices(),
            $namespaceClient->getReplicationControllerRepository()->findAll()->getReplicationControllers()
        );

        return new Environment(
            $namespaceMetadata->getName(),
            $namespaceMetadata->getName(),
            $this->componentTransformer->getComponentsFromObjectList($objects)
        );
    }
}
