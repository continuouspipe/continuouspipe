<?php

namespace AppBundle\Controller;

use ContinuousPipe\HealthChecker\HealthCheckerClient;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Team\Team;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route(service="app.controller.cluster")
 */
class ClusterController
{
    /**
     * @var HealthCheckerClient
     */
    private $healthCheckerClient;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param HealthCheckerClient $healthCheckerClient
     * @param BucketRepository    $bucketRepository
     */
    public function __construct(HealthCheckerClient $healthCheckerClient, BucketRepository $bucketRepository)
    {
        $this->healthCheckerClient = $healthCheckerClient;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * Get the cluster health.
     *
     * @Route("/teams/{slug}/clusters/{clusterIdentifier}/health", methods={"GET"})
     * @ParamConverter("team", converter="team", options={"slug"="slug"})
     * @Security("is_granted('READ', team)")
     * @View
     */
    public function healthAction(Team $team, string $clusterIdentifier)
    {
        $cluster = $this->bucketRepository->find($team->getBucketUuid())->getClusters()->filter(function (Cluster $cluster) use ($clusterIdentifier) {
            return $cluster->getIdentifier() == $clusterIdentifier;
        })->first();

        if (false === $cluster) {
            throw new NotFoundHttpException(sprintf('Cluster named "%s" not found in team', $clusterIdentifier));
        }

        return $this->healthCheckerClient->findProblems($cluster);
    }
}
