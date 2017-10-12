<?php

namespace ContinuousPipe\Pipe\Kubernetes\Client;

use ContinuousPipe\Security\Credentials\Cluster;
use Kubernetes\Client\Client;

interface KubernetesClientFactory
{
    /**
     * @param Cluster\Kubernetes $cluster
     *
     * @return Client
     */
    public function getByCluster(Cluster\Kubernetes $cluster);
}
