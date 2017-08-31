<?php

namespace ContinuousPipe\River\ClusterPolicies\DefaultCluster;

use ContinuousPipe\River\Pipe\DeploymentRequest\Cluster\ClusterResolver;
use ContinuousPipe\River\Pipe\DeploymentRequest\Cluster\TargetClusterResolver;
use ContinuousPipe\River\Pipe\EnvironmentAwareConfiguration;
use ContinuousPipe\River\Tide;
use ContinuousPipe\Security\Credentials\Cluster;

class ClusterFromDefaultBucketIfNoConfiguration implements TargetClusterResolver
{
    /**
     * @var TargetClusterResolver
     */
    private $decoratedResolver;

    /**
     * @var ClusterResolver
     */
    private $clusterResolver;

    /**
     * @param TargetClusterResolver $decoratedResolver
     * @param ClusterResolver $clusterResolver
     */
    public function __construct(TargetClusterResolver $decoratedResolver, ClusterResolver $clusterResolver)
    {
        $this->decoratedResolver = $decoratedResolver;
        $this->clusterResolver = $clusterResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getClusterIdentifier(Tide $tide, EnvironmentAwareConfiguration $configuration): Cluster
    {
        if ($configuration->getClusterIdentifier() !== null) {
            return $this->decoratedResolver->getClusterIdentifier($tide, $configuration);
        }

        $clusters = $this->clusterResolver->findAll($tide->getTeam());

        if ($clusters->count() === 0) {
            throw new ClusterResolutionException('You do not have any cluster to deploy to. Add a cluster in your project.');
        }

        if ($clusters->count() === 1) {
            return $clusters[0]->getIdentifier();
        }

        foreach ($clusters as $cluster) {
            if ($this->hasDefaultPolicy($cluster)) {
                return $cluster;
            }
        }

        throw new ClusterResolutionException('You have multiple clusters, and no default cluster. Please set a default cluster or specify the cluster in your deployment configuration.');
    }

    private function hasDefaultPolicy(Cluster $cluster) : bool
    {
        foreach ($cluster->getPolicies() as $policy) {
            if ($policy->getName() == "default") {
                return true;
            }
        }

        return false;
    }
}
