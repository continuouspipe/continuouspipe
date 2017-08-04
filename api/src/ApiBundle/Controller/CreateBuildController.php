<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Builder\Aggregate\Build as AggregateBuild;
use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Aggregate\BuildFactory;
use ContinuousPipe\Builder\Aggregate\Command\StartGcbBuild;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Engine;
use ContinuousPipe\Builder\Image\ExistingImageChecker;
use ContinuousPipe\Builder\Image\SearchingForExistingImageException;
use ContinuousPipe\Builder\Notifier;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;
use ContinuousPipe\Builder\View\BuildViewRepository;
use FOS\RestBundle\Controller\Annotations\View;
use Psr\Log\LoggerInterface;
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
     * @var ExistingImageChecker
     */
    private $imageChecker;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @param MessageBus $commandBus
     * @param ValidatorInterface $validator
     * @param BuildFactory $buildFactory
     * @param BuildViewRepository $buildViewRepository
     * @param BuildRequestTransformer $buildRequestTransformer
     * @param ExistingImageChecker $imageChecker
     */
    public function __construct(
        MessageBus $commandBus,
        ValidatorInterface $validator,
        BuildFactory $buildFactory,
        BuildViewRepository $buildViewRepository,
        BuildRequestTransformer $buildRequestTransformer,
        ExistingImageChecker $imageChecker,
        LoggerInterface $logger,
        Notifier $notifier
    ) {
        $this->commandBus = $commandBus;
        $this->validator = $validator;
        $this->buildFactory = $buildFactory;
        $this->buildViewRepository = $buildViewRepository;
        $this->buildRequestTransformer = $buildRequestTransformer;
        $this->imageChecker = $imageChecker;
        $this->logger = $logger;
        $this->notifier = $notifier;
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

        if (null === $request->getEngine()) {
            $request = $request->withEngine(new Engine('gcb'));
        }

        $build = $this->createAndStartBuild($request);

        return $this->buildViewRepository->find($build->getIdentifier());
    }

    private function createAndStartBuild(BuildRequest $request) : AggregateBuild
    {
        $build = $this->buildFactory->fromRequest(
            $this->buildRequestTransformer->transform($request)
        );

        try {
            if ($this->imageChecker->checkIfImagesExist($build)) {
                $notification = $build->getRequest()->getNotification();
                $this->notifier->notify($notification, $this->convertToSimpleBuild($build));

                return $build;
            }
        } catch (\Throwable $exception) {
            $this->logger->warning('Something went wrong while checking for existing image', [$exception]);
        }

        $this->commandBus->handle(new StartGcbBuild($build->getIdentifier()));

        return $build;
    }

    private function convertToSimpleBuild(AggregateBuild $aggregateBuild) : Build
    {
        return new Build(
            $aggregateBuild->getIdentifier(),
            $aggregateBuild->getRequest(),
            $aggregateBuild->getUser(),
            $aggregateBuild->getStatus()
        );
    }
}
