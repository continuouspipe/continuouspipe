<?php

namespace AdminBundle\Controller;

use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamRepository;
use GuzzleHttp\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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

        if (!$cluster instanceof Kubernetes) {
            throw new UnprocessableEntityHttpException('Cannot display the page of a non Kubernetes cluster');
        }

        $httpClient = new Client();
        throw new \Exception("This needs customisation for your installation");

        $status = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        return [
            'team' => $team,
            'cluster' => $cluster,
            'clusterStatus' => $status,
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
