<?php

namespace ContinuousPipe\River\ClusterPolicies\ClusterResolution;

use ContinuousPipe\River\ClusterPolicies\ClusterPolicyException;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Team\Team;

class GetFromClusterInTeamBucket implements ClusterPolicyResolver
{
    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param BucketRepository $bucketRepository
     */
    public function __construct(BucketRepository $bucketRepository)
    {
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find(Team $team, string $clusterIdentifier, string $policyName)
    {
        $cluster = $this->clusterFromTeamBucket($team, $clusterIdentifier);

        foreach ($cluster->getPolicies() as $policy) {
            if ($policy->getName() == $policyName) {
                return $policy;
            }
        }

        return null;
    }

    /**
     * @param Team $team
     * @param string $clusterIdentifier
     *
     * @throws ClusterPolicyException
     *
     * @return Cluster
     */
    private function clusterFromTeamBucket(Team $team, string $clusterIdentifier)
    {
        $bucket = $this->bucketRepository->find($team->getBucketUuid());

        foreach ($bucket->getClusters() as $cluster) {
            if ($cluster->getIdentifier() == $clusterIdentifier) {
                return $cluster;
            }
        }

        throw new ClusterPolicyException(sprintf('Cluster "%s" was not found in team "%s"', $clusterIdentifier, $team->getName() ?: $team->getSlug()));
    }
}
