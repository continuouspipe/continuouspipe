<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\BuildRepository;
use ContinuousPipe\Builder\Command\StartBuildCommand;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\Authenticator\UserContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SimpleBus\Message\Bus\MessageBus;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param MessageBus         $commandBus
     * @param BuildRepository    $buildRepository
     * @param UserContext        $userContext
     * @param ValidatorInterface $validator
     */
    public function __construct(MessageBus $commandBus, BuildRepository $buildRepository, UserContext $userContext, ValidatorInterface $validator)
    {
        $this->buildRepository = $buildRepository;
        $this->commandBus = $commandBus;
        $this->userContext = $userContext;
        $this->validator = $validator;
    }

    /**
     * @Route("/build", methods={"POST"})
     * @ParamConverter("request", converter="build_request")
     * @View
     */
    public function postAction(BuildRequest $request)
    {
        $violations = $this->validator->validate($request);
        if ($violations->count() > 0) {
            return \FOS\RestBundle\View\View::create($violations->get(0), 400);
        }

        $user = $this->userContext->getCurrent();

        $build = Build::fromRequest($request, $user);
        $this->buildRepository->save($build);

        $this->commandBus->handle(StartBuildCommand::forBuild($build));

        return $build->jsonSerialize();
    }
}
