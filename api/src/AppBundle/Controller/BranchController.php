<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Command\PinBranch;
use ContinuousPipe\River\Command\UnpinBranch;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Pipeline\PipelineNotFound;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\View\View as ViewResponse;
use FOS\RestBundle\Controller\Annotations\View;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route(service="app.controller.branch")
 */
class BranchController
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    public function __construct(MessageBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @Route("/flows/{uuid}/branch/{branchName}/pinned", methods={"POST"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @Security("is_granted('ADMIN', flow)")
     * @View(statusCode=204)
     */
    public function pinAction($uuid, $branchName)
    {
        $this->commandBus->handle(new PinBranch(Uuid::fromString($uuid), $branchName));
    }

    /**
     * @Route("/flows/{uuid}/branch/{branchName}/pinned", methods={"POST"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @Security("is_granted('ADMIN', flow)")
     * @View(statusCode=204)
     */
    public function unpinAction($uuid, $branchName)
    {
        $this->commandBus->handle(new UnpinBranch(Uuid::fromString($uuid), $branchName));
    }
}
