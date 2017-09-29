<?php

namespace BuilderBundle\Controller;

use BuilderBundle\Request\CompletedBuildRequest;
use ContinuousPipe\Builder\Aggregate\Command\CompleteBuild;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\GoogleContainerBuilder\GoogleContainerBuildStatus;
use ContinuousPipe\Builder\Request\CompleteBuildRequest;
use ContinuousPipe\Builder\View\BuildViewRepository;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route(service="builder.controller.complete_build")
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
     * @Route("/complete/{identifier}", methods={"POST"}, schemes={"https"}, name="complete_build")
     * @ParamConverter("request", converter="fos_rest.request_body")
     * @View
     */
    public function completedAction(string $identifier, CompletedBuildRequest $request)
    {
        $violations = $this->validator->validate($request);
        if ($violations->count() > 0) {
            return \FOS\RestBundle\View\View::create($violations->get(0), 400);
        }

        $this->commandBus->handle(new CompleteBuild(
            $identifier,
            new GoogleContainerBuildStatus($request->getStatus())
        ));

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
