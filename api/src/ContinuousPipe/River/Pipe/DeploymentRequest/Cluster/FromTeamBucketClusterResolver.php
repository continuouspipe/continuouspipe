<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest\Cluster;

use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Team\Team;
use Doctrine\Common\Collections\Collection;

class FromTeamBucketClusterResolver implements ClusterResolver
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
    public function findAll(Team $team): Collection
    {
        try {
            return $this->bucketRepository->find($team->getBucketUuid())->getClusters();
        } catch (BucketNotFound $e) {
            throw new ClusterResolutionException('Can\'t get access to team\'s clusters', $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find(Team $team, string $clusterIdentifier): Cluster
    {
        foreach ($this->findAll($team) as $cluster) {
            if ($cluster->getIdentifier() == $clusterIdentifier) {
                return $cluster;
            }
        }

        throw new ClusterResolutionException(sprintf(
            'Cluster with identifier "%s" was not found in team "%s"',
            $clusterIdentifier,
            $team->getSlug()
        ));
    }
}
