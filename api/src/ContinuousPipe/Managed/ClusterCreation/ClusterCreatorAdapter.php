<?php

namespace ContinuousPipe\Managed\ClusterCreation;

use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Team\Team;

class ClusterCreatorAdapter implements ClusterCreator
{
    /**
     * @var array|ClusterCreator[]
     */
    private $creators;

    /**
     * @param ClusterCreator[] $creators
     */
    public function __construct(array $creators = [])
    {
        $this->creators = $creators;
    }

    /**
     * {@inheritdoc}
     */
    public function createForTeam(Team $team, string $clusterIdentifier, string $dsn): Cluster
    {
        foreach ($this->creators as $creator) {
            if ($creator->supports($team, $clusterIdentifier, $dsn)) {
                return $creator->createForTeam($team, $clusterIdentifier, $dsn);
            }
        }

        throw new ClusterCreationException('No creator supports the creation of such cluster.');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Team $team, string $clusterIdentifier, string $dsn): bool
    {
        foreach ($this->creators as $creator) {
            if ($creator->supports($team, $clusterIdentifier, $dsn)) {
                return true;
            }
        }

        return false;
    }
}
