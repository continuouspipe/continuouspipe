<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\EnvironmentClient;
use ContinuousPipe\Model\Environment;
use Kubernetes\Client\Client;

class KubernetesEnvironmentClient implements EnvironmentClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
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
}
