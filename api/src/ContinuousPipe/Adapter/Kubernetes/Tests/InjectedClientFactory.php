<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests;

use ContinuousPipe\Adapter\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Adapter\Kubernetes\KubernetesProvider;
use Kubernetes\Client\Client;

class InjectedClientFactory implements KubernetesClientFactory
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
    public function getByProvider(KubernetesProvider $provider)
    {
        return $this->client;
    }
}
