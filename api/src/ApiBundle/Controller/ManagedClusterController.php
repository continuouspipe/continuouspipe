<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Managed\ClusterCreation\ClusterCreationException;
use ContinuousPipe\Managed\ClusterCreation\ClusterCreationUserException;
use ContinuousPipe\Managed\ClusterCreation\ClusterCreator;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketContainer;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Team\Team;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route(service="api.controller.managed_cluster")
 */
class ManagedClusterController
{
    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @var ClusterCreator
     */
    private $clusterCreator;

    /**
     * @param BucketRepository $bucketRepository
     * @param ClusterCreator $clusterCreator
     */
    public function __construct(BucketRepository $bucketRepository, ClusterCreator $clusterCreator)
    {
        $this->bucketRepository = $bucketRepository;
        $this->clusterCreator = $clusterCreator;
    }

    /**
     * @Route("/teams/{slug}/managed/create-cluster", methods={"POST"})
     * @ParamConverter("team", converter="team")
     * @Security("is_granted('ADMIN', team)")
     * @View(statusCode=201)
     */
    public function createClusterAction(Team $team)
    {
        $bucket = $this->bucketRepository->find($team->getBucketUuid());

        if ($this->hasCluster($bucket, 'managed')) {
            return new JsonResponse([
                'message' => 'You already have a cluster named "managed" in your project',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $cluster = $this->clusterCreator->createForTeam($team, 'managed');
        } catch (ClusterCreationUserException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 400);
        } catch (ClusterCreationException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], 500);
        }

        $bucket->getClusters()->add($cluster);
        $this->bucketRepository->save($bucket);

        return $cluster;
    }

    private function hasCluster(Bucket $bucket, string $identifier) : bool
    {
        foreach ($bucket->getClusters() as $cluster) {
            if ($cluster->getIdentifier() == $identifier) {
                return true;
            }
        }

        return false;
    }
}
