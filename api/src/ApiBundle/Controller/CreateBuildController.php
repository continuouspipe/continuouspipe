<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Builder\Aggregate\BuildFactory;
use ContinuousPipe\Builder\Aggregate\Command\StartBuild;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Request\BuildRequestException;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;
use ContinuousPipe\Builder\View\BuildViewRepository;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Events\Transaction\TransactionManager;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        try {
            $request = $this->buildRequestTransformer->transform($request);
        } catch (BuildRequestException $e) {
            return new JsonResponse([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ],
            ], 400);
        }

        if (null === ($engine = $request->getEngine())) {
            $referenceBuild =  $this->createBuild($request, 'docker');
            $this->createHiddenGcbBuild($request);
        } else {
            $referenceBuild =  $this->createBuild($request, $engine);
        }

        $this->commandBus->handle(new StartBuild($referenceBuild->getIdentifier()));

        return $this->buildViewRepository->find($referenceBuild->getIdentifier());
    }

    /**
     * @param BuildRequest $request
     */
    private function createHiddenGcbBuild(BuildRequest $request)
    {
        $request = $request->withSteps(array_map(
            function (BuildStepConfiguration $step) {
                return $step->withReadArtifacts(
                    array_map(
                        function (Artifact $artifact) {
                            return new Artifact($artifact->getIdentifier() . '-gcb', $artifact->getPath());
                        },
                        $step->getReadArtifacts()
                    )
                )->withImage(
                    $step->getImage()->withTag($step->getImage()->getTag() . '-gcb')
                )->withLogStreamIdentifier($step->getLogStreamIdentifier() . '/gcb');
            },
            $request->getSteps()
        ));

        $request = $request->withParentLogIdentifier(
            $request->getLogging()->getLogStream()->getParentLogIdentifier() . '/gcb'
        );

        $gcbBuild = $this->createBuild($request, 'gcb');

        $this->commandBus->handle(new StartBuild($gcbBuild->getIdentifier()));
    }

    /**
     * @param BuildRequest $request
     * @return \ContinuousPipe\Builder\Aggregate\Build
     */
    private function createBuild(BuildRequest $request, $engine)
    {
        return $this->buildFactory->fromRequest($request, Uuid::uuid4()->toString() . '--' . $engine);
    }
}
