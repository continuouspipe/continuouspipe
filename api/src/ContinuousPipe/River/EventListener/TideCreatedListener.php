<?php

namespace ContinuousPipe\River\EventListener;

use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\Event\TideCreated;
use SimpleBus\Message\Bus\MessageBus;

class TideCreatedListener
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
     * @param TideCreated $event
     */
    public function notify(TideCreated $event)
    {
        $startCommand = new StartTideCommand($event->getTideUuid());

        $this->commandBus->handle($startCommand);
    }
}
