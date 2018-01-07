<?php

namespace ContinuousPipe\River\Recover\TimedOutTides\EventListener;

use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Recover\TimedOutTides\Event\TideTimedOut;
use SimpleBus\Message\Bus\MessageBus;

class FailTimedOutTidesListener
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param MessageBus $eventBus
     */
    public function __construct(MessageBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @param TideTimedOut $event
     */
    public function notify(TideTimedOut $event)
    {
        $this->eventBus->handle(new TideFailed($event->getTideUuid(), 'Timed out'));
    }
}
