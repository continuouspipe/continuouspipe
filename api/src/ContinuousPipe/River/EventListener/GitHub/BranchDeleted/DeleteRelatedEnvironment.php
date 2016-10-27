<?php

namespace ContinuousPipe\River\EventListener\GitHub\BranchDeleted;

use ContinuousPipe\River\Command\DeleteEnvironments;
use ContinuousPipe\River\Event\GitHub\BranchDeleted;
use SimpleBus\Message\Bus\MessageBus;

class DeleteRelatedEnvironment
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
     * @param BranchDeleted $event
     */
    public function notify(BranchDeleted $event)
    {
        $this->commandBus->handle(new DeleteEnvironments(
            $event->getFlow()->getUuid(),
            $event->getCodeReference()
        ));
    }
}
