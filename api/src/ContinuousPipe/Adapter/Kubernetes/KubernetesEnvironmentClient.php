<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\EnvironmentClient;
use ContinuousPipe\Adapter\EnvironmentNotFound;
use ContinuousPipe\Adapter\Kubernetes\Inspector\NamespaceInspector;
use ContinuousPipe\Model\Environment;
use Kubernetes\Client\Client;
use Kubernetes\Client\Exception\NamespaceNotFound;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\Label;

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
        $namespaces = $this->getNamespaceRepository()->findAll();
        $environments = [];

        foreach ($namespaces->getNamespaces() as $namespace) {
            $environments[] = $this->namespaceToEnvironment($namespace);
        }

        return $environments;
    }

    /**
     * {@inheritdoc}
     */
    public function findByLabels(array $labels)
    {
        $namespaceLabels = KeyValueObjectList::fromAssociativeArray($labels, Label::class);
        $namespaces = $this->getNamespaceRepository()->findByLabels($namespaceLabels);

        return array_map(function (KubernetesNamespace $namespace) {
            return $this->namespaceToEnvironment($namespace);
        }, $namespaces);
    }

    /**
     * {@inheritdoc}
     */
    public function find($identifier)
    {
        try {
            $namespace = $this->getNamespaceRepository()->findOneByName($identifier);
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
        $namespaceRepository = $this->getNamespaceRepository();
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

    /**
     * @return \Kubernetes\Client\Repository\NamespaceRepository
     */
    private function getNamespaceRepository()
    {
        return $this->client->getNamespaceRepository();
    }
}
