<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Builder\Aggregate\BuildFactory;
use ContinuousPipe\Builder\Aggregate\Command\StartBuild;
use ContinuousPipe\Builder\Request\BuildRequestException;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;
use ContinuousPipe\Builder\View\BuildViewRepository;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Events\Transaction\TransactionManager;
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
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ], 400);
        }

        $build = $this->buildFactory->fromRequest($request);

        $this->commandBus->handle(new StartBuild($build->getIdentifier()));

        return $this->buildViewRepository->find($build->getIdentifier());
    }
}
