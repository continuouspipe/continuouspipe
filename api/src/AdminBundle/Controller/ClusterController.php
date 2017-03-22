<?php

namespace AdminBundle\Controller;

use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route(service="admin.controller.cluster")
 */
class ClusterController
{
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param TeamRepository $teamRepository
     * @param BucketRepository $bucketRepository
     */
    public function __construct(TeamRepository $teamRepository, BucketRepository $bucketRepository)
    {
        $this->teamRepository = $teamRepository;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * @Route("/teams/{team}/cluster/{clusterIdentifier}", name="admin_team_cluster")
     * @ParamConverter("team", converter="team", options={"slug"="team"})
     * @Template
     */
    public function showAction(Team $team, string $clusterIdentifier)
    {
        if (null === $cluster = $this->findCluster($team, $clusterIdentifier)) {
            throw new NotFoundHttpException(sprintf(
                'Cluster "%s" not found',
                $clusterIdentifier
            ));
        }

        return [
            'team' => $team,
            'cluster' => $cluster,
        ];
    }

    private function findCluster(Team $team, string $clusterIdentifier)
    {
        $clusters = $this->bucketRepository->find($team->getBucketUuid())->getClusters();

        foreach ($clusters as $cluster) {
            if ($cluster->getIdentifier() == $clusterIdentifier) {
                return $cluster;
            }
        }

        return null;
    }
}
