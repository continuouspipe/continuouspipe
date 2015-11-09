<?php

namespace ContinuousPipe\Adapter;

use ContinuousPipe\Security\Credentials\Cluster;

interface EnvironmentClientFactory
{
    /**
     * @param Provider $provider
     *
     * @deprecated
     *
     * @return EnvironmentClient
     */
    public function getByProvider(Provider $provider);

    /**
     * Get an environment client for this given cluster.
     *
     * @param Cluster $cluster
     *
     * @return EnvironmentClient
     */
    public function getByCluster(Cluster $cluster);
}
