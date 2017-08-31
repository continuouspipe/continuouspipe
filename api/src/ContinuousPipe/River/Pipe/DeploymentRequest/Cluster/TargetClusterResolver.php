<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest\Cluster;

use ContinuousPipe\River\Pipe\EnvironmentAwareConfiguration;
use ContinuousPipe\River\Tide;
use ContinuousPipe\Security\Credentials\Cluster;

interface TargetClusterResolver
{
    /**
     * @param Tide $tide
     * @param EnvironmentAwareConfiguration $configuration
     *
     * @throws ClusterResolutionException
     *
     * @return Cluster
     */
    public function getClusterIdentifier(Tide $tide, EnvironmentAwareConfiguration $configuration) : Cluster;
}
