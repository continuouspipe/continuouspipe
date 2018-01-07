<?php

namespace ContinuousPipe\Managed\ClusterCreation;

use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Team\Team;

interface ClusterCreator
{
    /**
     * @param Team $team
     * @param string $clusterIdentifier
     *
     * @throws ClusterCreationException
     *
     * @return Cluster
     */
    public function createForTeam(Team $team, string $clusterIdentifier) : Cluster;
}
