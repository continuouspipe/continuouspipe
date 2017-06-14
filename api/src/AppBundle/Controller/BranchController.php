<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeRepository\Branch;
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
     * @Route("/flows/{uuid}/pinned-branch", methods={"POST"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @ParamConverter("branch", converter="fos_rest.request_body")
     * @Security("is_granted('ADMIN', flow)")
     * @View(statusCode=204)
     */
    public function pinAction($uuid, Branch $branch)
    {
        $this->commandBus->handle(new PinBranch(Uuid::fromString($uuid), (string) $branch));
    }

    /**
     * @Route("/flows/{uuid}/pinned-branch", methods={"DELETE"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @ParamConverter("branch", converter="fos_rest.request_body")
     * @Security("is_granted('ADMIN', flow)")
     * @View(statusCode=204)
     */
    public function unpinAction($uuid, Branch $branch)
    {
        $this->commandBus->handle(new UnpinBranch(Uuid::fromString($uuid), (string) $branch));
    }
}
