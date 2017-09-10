<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest\Cluster;

use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Team\Team;
use Doctrine\Common\Collections\Collection;

interface ClusterResolver
{
    /**
     * @param Team $team
     *
     * @throws ClusterResolutionException
     *
     * @return Cluster[]|Collection
     */
    public function findAll(Team $team) : Collection;

    /**
     * @param Team $team
     * @param string $clusterIdentifier
     *
     * @throws ClusterResolutionException
     *
     * @return Cluster
     */
    public function find(Team $team, string $clusterIdentifier) : Cluster;
}
