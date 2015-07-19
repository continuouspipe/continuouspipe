<?php

namespace ContinuousPipe\River\EventListener;

use ContinuousPipe\River\Command\CodeRepository\SetupWebHookCommand;
use ContinuousPipe\River\Event\FlowCreated;
use League\Tactician\CommandBus;

class FlowCreatedListener
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param FlowCreated $event
     */
    public function notify(FlowCreated $event)
    {
        $flow = $event->getFlow();

        $this->commandBus->handle(new SetupWebHookCommand(
            $flow->getRepository(),
            $flow->getUser()
        ));
    }
}
