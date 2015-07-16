<?php

namespace AppBundle\Controller;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\BuildRepository;
use ContinuousPipe\Builder\Command\BuildCommand;
use ContinuousPipe\Builder\Request\BuildRequest;
use League\Tactician\CommandBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @param CommandBus $commandBus
     * @param BuildRepository $buildRepository
     */
    public function __construct(CommandBus $commandBus, BuildRepository $buildRepository)
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
