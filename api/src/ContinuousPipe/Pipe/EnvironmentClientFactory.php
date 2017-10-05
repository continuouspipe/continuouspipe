<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Pipe\ClusterNotSupported;
use ContinuousPipe\Pipe\EnvironmentClient;
use ContinuousPipe\Security\Credentials\Cluster;

interface EnvironmentClientFactory
{
    /**
     * Get an environment client for this given cluster.
     *
     * @param Cluster $cluster
     *
     * @throws ClusterNotSupported
     *
     * @return EnvironmentClient
     */
    public function getByCluster(Cluster $cluster);
}
