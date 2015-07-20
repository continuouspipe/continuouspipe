<?php

namespace ContinuousPipe\River\EventListener;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Command\BuildImagesCommand;
use ContinuousPipe\River\Event\TideStarted;
use SimpleBus\Message\Bus\MessageBus;

class TideStartedListener
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
     * @param TideStarted $event
     */
    public function notify(TideStarted $event)
    {
        $this->commandBus->handle(new BuildImagesCommand($event->getTideUuid()));
    }
}
