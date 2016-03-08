<?php

namespace ContinuousPipe\River\Recover\TimedOutTides\EventListener;

use ContinuousPipe\River\CommandBus\DelayedCommandBus;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Recover\TimedOutTides\Command\SpotTimedOutTidesCommand;
use ContinuousPipe\River\View\TideRepository;

class QueueSpotTimedOutWhenTideStartedListener
{
    /**
     * @var DelayedCommandBus
     */
    private $delayedCommandBus;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var int
     */
    private $tideTimeout;

    /**
     * @param DelayedCommandBus $delayedCommandBus
     * @param TideRepository    $tideRepository
     * @param int               $tideTimeout
     */
    public function __construct(DelayedCommandBus $delayedCommandBus, TideRepository $tideRepository, $tideTimeout)
    {
        $this->delayedCommandBus = $delayedCommandBus;
        $this->tideRepository = $tideRepository;
        $this->tideTimeout = $tideTimeout;
    }

    /**
     * @param TideStarted $event
     */
    public function notify(TideStarted $event)
    {
        $tide = $this->tideRepository->find($event->getTideUuid());

        $this->delayedCommandBus->publish(
            new SpotTimedOutTidesCommand($tide->getFlow()->getUuid()),
            $this->tideTimeout
        );
    }
}
