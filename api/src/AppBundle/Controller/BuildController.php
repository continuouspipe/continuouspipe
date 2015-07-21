<?php

namespace AppBundle\Controller;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\BuildRepository;
use ContinuousPipe\Builder\Command\BuildCommand;
use ContinuousPipe\Builder\Request\BuildRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route(service="app.controller.build")
 */
class BuildController
{
    /**
     * @var BuildRepository
     */
    private $buildRepository;

    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param MessageBus      $commandBus
     * @param BuildRepository $buildRepository
     */
    public function __construct(MessageBus $commandBus, BuildRepository $buildRepository)
    {
        $this->buildRepository = $buildRepository;
        $this->commandBus = $commandBus;
    }

    /**
     * @Route("/build", methods={"POST"})
     * @ParamConverter("request", converter="build_request")
     */
    public function buildAction(BuildRequest $request)
    {
        $build = Build::fromRequest($request);
        $this->buildRepository->save($build);

        $this->commandBus->handle(BuildCommand::forBuild($build));

        return new JsonResponse($build);
    }
}
