<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\BuildRepository;
use ContinuousPipe\Builder\Command\BuildCommand;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\User\Security\UserContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SimpleBus\Message\Bus\MessageBus;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="api.controller.create_build")
 */
class CreateBuildController
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
     * @var UserContext
     */
    private $userContext;

    /**
     * @param MessageBus      $commandBus
     * @param BuildRepository $buildRepository
     * @param UserContext     $userContext
     */
    public function __construct(MessageBus $commandBus, BuildRepository $buildRepository, UserContext $userContext)
    {
        $this->buildRepository = $buildRepository;
        $this->commandBus = $commandBus;
        $this->userContext = $userContext;
    }

    /**
     * @Route("/build", methods={"POST"})
     * @ParamConverter("request", converter="build_request")
     * @View
     */
    public function postAction(BuildRequest $request)
    {
        $user = $this->userContext->getCurrent();

        $build = Build::fromRequest($request, $user);
        $this->buildRepository->save($build);

        $this->commandBus->handle(BuildCommand::forBuild($build));

        return $build->jsonSerialize();
    }
}
