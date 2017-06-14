<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Command\PinBranch;
use ContinuousPipe\River\Command\UnpinBranch;
use ContinuousPipe\River\Flow;
use FOS\RestBundle\Controller\Annotations\View;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SimpleBus\Message\Bus\MessageBus;

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
     * @Route("/flows/{uuid}/pinned-branch/{branchName}", methods={"PUT"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @Security("is_granted('ADMIN', flow)")
     * @View(statusCode=204)
     */
    public function pinAction($uuid, $branchName)
    {
        $this->commandBus->handle(new PinBranch(Uuid::fromString($uuid), $branchName));
    }

    /**
     * @Route("/flows/{uuid}/pinned-branch/{branchName}", methods={"DELETE"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @Security("is_granted('ADMIN', flow)")
     * @View(statusCode=204)
     */
    public function unpinAction($uuid, $branchName)
    {
        $this->commandBus->handle(new UnpinBranch(Uuid::fromString($uuid), $branchName));
    }
}
