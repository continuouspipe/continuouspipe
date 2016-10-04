<?php

namespace ContinuousPipe\River\Tide\Concurrency\Saga;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Tide\Concurrency\Lock\Locker;
use ContinuousPipe\River\TideSaga;

class LockTideWhileSaga implements TideSaga
{
    /**
     * @var TideSaga
     */
    private $decoratedSaga;

    /**
     * @var Locker
     */
    private $locker;

    /**
     * @param TideSaga $decoratedSaga
     * @param Locker   $locker
     */
    public function __construct(TideSaga $decoratedSaga, Locker $locker)
    {
        $this->decoratedSaga = $decoratedSaga;
        $this->locker = $locker;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(TideEvent $event)
    {
        return $this->locker->lock(
            sprintf('tide-'.(string) $event->getTideUuid()),
            function () use ($event) {
                return $this->decoratedSaga->notify($event);
            }
        );
    }
}
