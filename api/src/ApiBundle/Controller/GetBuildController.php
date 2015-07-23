<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Builder\BuildRepository;
use Rhumsaa\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route(service="api.controller.get_build")
 */
class GetBuildController
{
    /**
     * @var BuildRepository
     */
    private $buildRepository;

    /**
     * @param BuildRepository $buildRepository
     */
    public function __construct(BuildRepository $buildRepository)
    {
        $this->buildRepository = $buildRepository;
    }

    /**
     * @Route("/build/{uuid}")
     */
    public function getAction($uuid)
    {
        $build = $this->buildRepository->find(Uuid::fromString($uuid));

        return new JsonResponse($build);
    }
}
