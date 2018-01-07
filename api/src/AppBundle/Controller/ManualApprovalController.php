<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Task\ManualApproval\Command\Approve;
use ContinuousPipe\River\Task\ManualApproval\Command\Reject;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\Security\User\User;
use SimpleBus\Message\Bus\MessageBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route("/tides/{uuid}", service="app.controller.manual_approval")
 * @ParamConverter("tide", converter="tide", options={"identifier"="uuid"})
 * @Security("is_granted('READ', tide)")
 */
class ManualApprovalController
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param MessageBus $commandBus
     */
    public function __construct(MessageBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @Route("/tasks/{taskIdentifier}/{choice}", requirements={"choice"="reject|approve"})
     * @ParamConverter("user", converter="user")
     * @View
     */
    public function choiceAction(Tide $tide, string $taskIdentifier, string $choice, User $user)
    {
        $commandClass = $choice == 'approve' ? Approve::class : Reject::class;
        $command = new $commandClass($tide->getUuid(), $taskIdentifier, $user);

        $this->commandBus->handle($command);
    }
}
