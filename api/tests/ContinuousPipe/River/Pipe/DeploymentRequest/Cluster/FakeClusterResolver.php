<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest\Cluster;

use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Team\Team;
use Doctrine\Common\Collections\Collection;

class FakeClusterResolver implements ClusterResolver
{
    /**
     * @var ClusterResolver
     */
    private $decoratedResolver;

    /**
     * @param ClusterResolver $decoratedResolver
     */
    public function __construct(ClusterResolver $decoratedResolver)
    {
        $this->decoratedResolver = $decoratedResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(Team $team): Collection
    {
        return $this->decoratedResolver->findAll($team);
    }

    /**
     * {@inheritdoc}
     */
    public function find(Team $team, string $clusterIdentifier): Cluster
    {
        try {
            return $this->decoratedResolver->find($team, $clusterIdentifier);
        } catch (ClusterResolutionException $e) {
            return new Cluster\Kubernetes(
                $clusterIdentifier,
                'https://1.2.3.4',
                'v1.6',
                'username',
                'password'
            );
        }
    }
}
