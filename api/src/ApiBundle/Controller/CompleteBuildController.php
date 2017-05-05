<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Builder\Aggregate\Command\CompleteBuild;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Request\CompleteBuildRequest;
use ContinuousPipe\Builder\View\BuildViewRepository;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route(service="api.controller.complete_build")
 */
class CompleteBuildController
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var BuildViewRepository
     */
    private $buildViewRepository;

    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param MessageBus $commandBus
     * @param ValidatorInterface $validator
     * @param BuildViewRepository $buildViewRepository
     */
    public function __construct(
        MessageBus $commandBus,
        ValidatorInterface $validator,
        BuildViewRepository $buildViewRepository
    ) {
        $this->commandBus = $commandBus;
        $this->validator = $validator;
        $this->buildViewRepository = $buildViewRepository;
    }

    /**
     * @Route("/complete", methods={"POST"})
     * @ParamConverter("request", converter="complete_build_request")
     * @View
     */
    public function postAction(CompleteBuildRequest $request)
    {
        $violations = $this->validator->validate($request);
        if ($violations->count() > 0) {
            return \FOS\RestBundle\View\View::create($violations->get(0), 400);
        }

        $this->commandBus->handle(new CompleteBuild($request->getBuildId(), $request->getStatus()));

        return $this->buildViewRepository->find($request->getBuildId());
    }

}
