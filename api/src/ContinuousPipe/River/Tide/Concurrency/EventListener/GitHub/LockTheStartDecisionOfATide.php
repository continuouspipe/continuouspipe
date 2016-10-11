<?php

namespace ContinuousPipe\River\Tide\Concurrency\EventListener\GitHub;

use ContinuousPipe\River\Event\CodeRepositoryEvent;
use ContinuousPipe\River\EventListener\GitHub\EventuallyCreateAndStartTide;
use ContinuousPipe\River\Tide\Concurrency\Lock\Locker;

class LockTheStartDecisionOfATide
{
    /**
     * @var EventuallyCreateAndStartTide
     */
    private $eventuallyCreateAndStartTideListener;

    /**
     * @var Locker
     */
    private $locker;

    /**
     * @param EventuallyCreateAndStartTide $eventuallyCreateAndStartTideListener
     * @param Locker $locker
     */
    public function __construct(EventuallyCreateAndStartTide $eventuallyCreateAndStartTideListener, Locker $locker)
    {
        $this->eventuallyCreateAndStartTideListener = $eventuallyCreateAndStartTideListener;
        $this->locker = $locker;
    }

    /**
     * @param CodeRepositoryEvent $event
     */
    public function notify(CodeRepositoryEvent $event)
    {
        $this->locker->lock(
            sprintf('flow-'.(string) $event->getFlow()->getUuid()),
            function () use ($event) {
                return $this->eventuallyCreateAndStartTideListener->notify($event);
            }
        );
    }
}
