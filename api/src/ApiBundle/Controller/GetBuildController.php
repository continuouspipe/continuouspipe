<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Builder\View\BuildViewRepository;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route(service="api.controller.get_build")
 */
class GetBuildController
{
    /**
     * @var BuildViewRepository
     */
    private $buildRepository;

    /**
     * @param BuildViewRepository $buildRepository
     */
    public function __construct(BuildViewRepository $buildRepository)
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
