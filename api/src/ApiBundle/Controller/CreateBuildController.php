<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Builder\Aggregate\BuildFactory;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;
use ContinuousPipe\Builder\View\BuildViewRepository;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Events\Transaction\TransactionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
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
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @var BuildViewRepository
     */
    private $buildViewRepository;
    /**
     * @var BuildRequestTransformer
     */
    private $buildRequestTransformer;

    /**
     * @param TransactionManager $transactionManager
     * @param ValidatorInterface $validator
     * @param BuildFactory $buildFactory
     * @param BuildViewRepository $buildViewRepository
     * @param BuildRequestTransformer $buildRequestTransformer
     */
    public function __construct(
        TransactionManager $transactionManager,
        ValidatorInterface $validator,
        BuildFactory $buildFactory,
        BuildViewRepository $buildViewRepository,
        BuildRequestTransformer $buildRequestTransformer
    ) {
        $this->validator = $validator;
        $this->buildFactory = $buildFactory;
        $this->transactionManager = $transactionManager;
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

        $request = $this->buildRequestTransformer->transform($request);
        $build = $this->buildFactory->fromRequest($request);

        $this->transactionManager->apply($build->getIdentifier(), function (\ContinuousPipe\Builder\Aggregate\Build $build) {
            $build->start();
        });

        return $this->buildViewRepository->find($build->getIdentifier());
    }
}
