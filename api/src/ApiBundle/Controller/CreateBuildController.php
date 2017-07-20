<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Aggregate\BuildFactory;
use ContinuousPipe\Builder\Aggregate\Command\StartGcbBuild;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Engine;
use ContinuousPipe\Builder\Image\ExistingImageChecker;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;
use ContinuousPipe\Builder\View\BuildViewRepository;
use FOS\RestBundle\Controller\Annotations\View;
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
        ExistingImageChecker $imageChecker
    ) {
        $this->commandBus = $commandBus;
        $this->validator = $validator;
        $this->buildFactory = $buildFactory;
        $this->buildViewRepository = $buildViewRepository;
        $this->buildRequestTransformer = $buildRequestTransformer;
        $this->imageChecker = $imageChecker;
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

    private function createAndStartBuild(BuildRequest $request) : Build
    {
        $build = $this->buildFactory->fromRequest(
            $this->buildRequestTransformer->transform($request)
        );

        //check if build already started here?
        //so use service to do it?
        //ok cool, so what do I need to do in the tests
        //add some way of checking if the build has alreayd been done for a single step build
        //make sure the correct stuff is logged
        //what about the response? might just be ok.
        //make it a service can always change where it is called from

        if ($this->imageChecker->checkIfImagesExist($build)) {

            return $build;
        }

        $this->commandBus->handle(new StartGcbBuild($build->getIdentifier()));

        return $build;
    }

}
