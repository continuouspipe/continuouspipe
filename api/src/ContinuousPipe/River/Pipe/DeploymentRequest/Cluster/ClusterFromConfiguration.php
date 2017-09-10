<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest\Cluster;

use ContinuousPipe\River\Pipe\EnvironmentAwareConfiguration;
use ContinuousPipe\River\Tide;
use ContinuousPipe\Security\Credentials\Cluster;

class ClusterFromConfiguration implements TargetClusterResolver
{
    /**
     * @var ClusterResolver
     */
    private $clusterResolver;

    /**
     * @param ClusterResolver $clusterResolver
     */
    public function __construct(ClusterResolver $clusterResolver)
    {
        $this->clusterResolver = $clusterResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getClusterIdentifier(Tide $tide, EnvironmentAwareConfiguration $configuration): Cluster
    {
        if (null === ($clusterIdentifier = $configuration->getClusterIdentifier())) {
            throw new ClusterResolutionException('The `cluster` to which to deploy needs to be configured');
        }

        return $this->clusterResolver->find($tide->getTeam(), $configuration->getClusterIdentifier());
    }
}
