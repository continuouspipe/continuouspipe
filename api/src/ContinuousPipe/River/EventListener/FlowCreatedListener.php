<?php

namespace ContinuousPipe\River\EventListener;

use ContinuousPipe\River\Command\CodeRepository\SetupWebHookCommand;
use ContinuousPipe\River\Event\FlowCreated;
use League\Tactician\CommandBus;
use SimpleBus\Message\Bus\MessageBus;

class FlowCreatedListener
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
