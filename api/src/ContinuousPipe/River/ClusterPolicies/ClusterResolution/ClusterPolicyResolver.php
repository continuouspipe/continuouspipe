<?php

namespace ContinuousPipe\River\ClusterPolicies\ClusterResolution;

use ContinuousPipe\Pipe\ClusterNotFound;
use ContinuousPipe\River\ClusterPolicies\ClusterPolicyException;
use ContinuousPipe\Security\Credentials\Cluster\ClusterPolicy;
use ContinuousPipe\Security\Team\Team;

interface ClusterPolicyResolver
{
    /**
     * @param Team   $team
     * @param string $clusterIdentifier
     * @param string $policyName
     *
     * @throws ClusterPolicyException
     * @throws ClusterNotFound
     *
     * @return ClusterPolicy|null
     */
    public function find(Team $team, string $clusterIdentifier, string $policyName);
}
