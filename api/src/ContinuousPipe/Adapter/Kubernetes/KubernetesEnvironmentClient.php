<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\EnvironmentClient;
use ContinuousPipe\Adapter\EnvironmentNotFound;
use ContinuousPipe\Adapter\Kubernetes\Inspector\NamespaceInspector;
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
     * @var NamespaceInspector
     */
    private $namespaceInspector;

    /**
     * @param Client             $client
     * @param NamespaceInspector $namespaceInspector
     */
    public function __construct(Client $client, NamespaceInspector $namespaceInspector)
    {
        $this->client = $client;
        $this->namespaceInspector = $namespaceInspector;
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

        return new Environment(
            $namespaceMetadata->getName(),
            $namespaceMetadata->getName(),
            $this->namespaceInspector->getComponents($namespaceClient)
        );
    }
}
