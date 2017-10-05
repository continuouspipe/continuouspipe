<?php

namespace ContinuousPipe\Pipe\Kubernetes\Tests;

use ContinuousPipe\Pipe\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Security\Credentials\Cluster;
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
     * @param Cluster\Kubernetes $cluster
     *
     * @return Client
     */
    public function getByCluster(Cluster\Kubernetes $cluster)
    {
        return $this->client;
    }
}
