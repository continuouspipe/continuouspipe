<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Aggregate\BuildFactory;
use ContinuousPipe\Builder\Aggregate\Command\StartBuild;
use ContinuousPipe\Builder\Aggregate\Command\StartGcbBuild;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Engine;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;
use ContinuousPipe\Builder\View\BuildViewRepository;
use FOS\RestBundle\Controller\Annotations\View;
use Inviqa\LaunchDarklyBundle\Client\ExplicitUser\StaticClient;
use LaunchDarkly\LDUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route(service="api.controller.create_build")
 */
class CreateBuildController
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var BuildFactory
     */
    private $buildFactory;

    /**
     * @var BuildViewRepository
     */
    private $buildViewRepository;
    /**
     * @var BuildRequestTransformer
     */
    private $buildRequestTransformer;
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param MessageBus $commandBus
     * @param ValidatorInterface $validator
     * @param BuildFactory $buildFactory
     * @param BuildViewRepository $buildViewRepository
     * @param BuildRequestTransformer $buildRequestTransformer
     */
    public function __construct(
        MessageBus $commandBus,
        ValidatorInterface $validator,
        BuildFactory $buildFactory,
        BuildViewRepository $buildViewRepository,
        BuildRequestTransformer $buildRequestTransformer
    ) {
        $this->commandBus = $commandBus;
        $this->validator = $validator;
        $this->buildFactory = $buildFactory;
        $this->buildViewRepository = $buildViewRepository;
        $this->buildRequestTransformer = $buildRequestTransformer;
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

        $userKey = $this->getUserKey($request);
        if (StaticClient::variation('main-gcb-build', new LDUser($userKey), false)) {
            $request = $request->withEngine(new Engine('gcb'));
        }
        if (null === $request->getEngine()) {
            $request = $request->withEngine(new Engine('gcb'));
        }

        $build = $this->createAndStartBuild($request);

        return $this->buildViewRepository->find($build->getIdentifier());
    }

    private function createAndStartBuild(BuildRequest $request) : Build
    {
        $build = $this->buildFactory->fromRequest(
            $this->buildRequestTransformer->transform($request)
        );

        if (StaticClient::variation('use-synchronous-gcb-build', new LDUser($this->getUserKey($request)), false)) {
            $this->commandBus->handle(new StartGcbBuild($build->getIdentifier()));

            return $build;
        }
        
        $this->commandBus->handle(new StartBuild($build->getIdentifier()));

        return $build;
    }

    private function getUserKey(BuildRequest $request)
    {
        $steps = $request->getSteps();
        if (!isset($steps[0])) {
            return 'builder';
        }

        $image = $steps[0]->getImage();
        if (!isset($image)) {
            return 'builder';
        }

        $imageName = $image->getName();
        if (!isset($imageName)) {
            return 'builder';
        }

        return $imageName;
    }
}
