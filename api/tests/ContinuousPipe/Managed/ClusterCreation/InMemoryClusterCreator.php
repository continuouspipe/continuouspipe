<?php

namespace ContinuousPipe\Managed\ClusterCreation;

use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Team\Team;

class InMemoryClusterCreator implements ClusterCreator
{
    /**
     * {@inheritdoc}
     */
    public function createForTeam(Team $team, string $clusterIdentifier): Cluster
    {
        return new Cluster\Kubernetes(
            $clusterIdentifier,
            'https://1.2.3.4',
            'v1.6'
        );
    }
}
