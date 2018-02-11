<?php

namespace ContinuousPipe\Managed\ClusterCreation;

use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Team\Team;

interface ClusterCreator
{
    /**
     * Create a cluster named $clusterIdentifier for the given team.
     *
     * @param Team $team
     * @param string $clusterIdentifier
     *
     * @throws ClusterCreationException
     *
     * @return Cluster
     */
    public function createForTeam(Team $team, string $clusterIdentifier, string $dsn) : Cluster;

    /**
     * Returns true if the cluster creator supports the given DSN.
     *
     * @param Team $team
     * @param string $clusterIdentifier
     * @param string $dsn
     *
     * @return bool
     */
    public function supports(Team $team, string $clusterIdentifier, string $dsn): bool;
}
