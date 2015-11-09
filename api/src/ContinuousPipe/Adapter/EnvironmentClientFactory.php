<?php

namespace ContinuousPipe\Adapter;

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
