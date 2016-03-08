<?php

namespace ContinuousPipe\River\Recover\TimedOutTides\EventListener;

use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Recover\TimedOutTides\Command\SpotTimedOutTidesCommand;
use SimpleBus\Message\Bus\MessageBus;

class SpotTimedOutTidesWhenTideCreatedListener
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
        $this->commandBus->handle(new SpotTimedOutTidesCommand(
            $event->getTideContext()->getFlowUuid()
        ));
    }
}
