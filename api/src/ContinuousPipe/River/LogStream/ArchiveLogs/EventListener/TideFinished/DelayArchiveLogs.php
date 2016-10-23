<?php

namespace ContinuousPipe\River\LogStream\ArchiveLogs\EventListener\TideFinished;

use ContinuousPipe\River\CommandBus\DelayedCommandBus;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\LogStream\ArchiveLogs\Command\ArchiveTideCommand;
use ContinuousPipe\River\View\TideRepository;

class DelayArchiveLogs
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var DelayedCommandBus
     */
    private $delayedCommandBus;

    /**
     * @var int
     */
    private $delay;

    /**
     * @param DelayedCommandBus $delayedCommandBus
     * @param TideRepository    $tideRepository
     * @param int               $delay
     */
    public function __construct(DelayedCommandBus $delayedCommandBus, TideRepository $tideRepository, $delay)
    {
        $this->tideRepository = $tideRepository;
        $this->delayedCommandBus = $delayedCommandBus;
        $this->delay = $delay;
    }

    /**
     * @param TideEvent $event
     */
    public function notify(TideEvent $event)
    {
        $tide = $this->tideRepository->find($event->getTideUuid());

        $command = new ArchiveTideCommand($tide->getUuid(), $tide->getLogId());
        $this->delayedCommandBus->publish($command, $this->delay);
    }
}
