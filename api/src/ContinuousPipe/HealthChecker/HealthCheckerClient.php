<?php

namespace ContinuousPipe\HealthChecker;

use ContinuousPipe\Security\Credentials\Cluster;

interface HealthCheckerClient
{
    /**
     * Find some problems on the given cluster.
     *
     * @param Cluster $cluster
     *
     * @return Problem[]
     */
    public function findProblems(Cluster $cluster);
}
