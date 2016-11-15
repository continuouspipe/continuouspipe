<?php

namespace AppBundle\Controller;

use AppBundle\Request\WatchRequest;
use ContinuousPipe\River\Environment\DeployedEnvironment;
use ContinuousPipe\River\Flow;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Watcher\Watcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ContinuousPipe\Watcher\WatcherException;

/**
 * @Route(service="app.controller.flow_environment")
 */
class FlowEnvironmentController
{
    /**
     * @var Flow\EnvironmentClient
     */
    private $environmentClient;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @var Watcher
     */
    private $watcher;

    /**
     * @param Flow\EnvironmentClient $environmentClient
     * @param BucketRepository       $bucketRepository
     * @param Watcher                $watcher
     */
    public function __construct(Flow\EnvironmentClient $environmentClient, BucketRepository $bucketRepository, Watcher $watcher)
    {
        $this->environmentClient = $environmentClient;
        $this->watcher = $watcher;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * @Route("/flows/{uuid}/environments", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @Security("is_granted('READ', flow)")
     * @View
     */
    public function listAction(Flow $flow)
    {
        return $this->environmentClient->findByFlow($flow);
    }

    /**
     * @Route("/flows/{uuid}/environments/{name}", methods={"DELETE"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @Security("is_granted('DELETE', flow)")
     * @View
     */
    public function deleteAction(Flow $flow, Request $request, $name)
    {
        $environment = new DeployedEnvironment($name, $request->query->get('cluster'));

        $this->environmentClient->delete($flow, $environment);
    }

    /**
     * @Route("/flows/{uuid}/environments/watch", methods={"POST"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @ParamConverter("watchRequest", converter="fos_rest.request_body")
     * @Security("is_granted('READ', flow)")
     * @View
     */
    public function watchAction(Flow $flow, WatchRequest $watchRequest)
    {
        $bucket = $this->bucketRepository->find($flow->getContext()->getTeam()->getBucketUuid());
        $clusters = $bucket->getClusters()->filter(function (Cluster $cluster) use ($watchRequest) {
            return $cluster->getIdentifier() == $watchRequest->getCluster();
        });

        if ($clusters->count() != 1) {
            throw new BadRequestHttpException(sprintf('Expected one cluster found %d', $clusters->count()));
        }

        try {
            return $this->watcher->logs(
                $clusters->first(),
                $watchRequest->getEnvironment(),
                $watchRequest->getPod()
            );
        } catch (WatcherException $e) {
            return new JsonResponse([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
