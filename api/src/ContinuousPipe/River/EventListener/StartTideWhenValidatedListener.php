<?php

namespace ContinuousPipe\River\EventListener;

use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\Event\TideValidated;
use SimpleBus\Message\Bus\MessageBus;

class StartTideWhenValidatedListener
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
     * @param TideValidated $event
     */
    public function notify(TideValidated $event)
    {
        $startCommand = new StartTideCommand($event->getTideUuid());

        $this->commandBus->handle($startCommand);
    }
}
