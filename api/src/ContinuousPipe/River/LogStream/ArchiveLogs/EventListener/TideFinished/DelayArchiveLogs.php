<?php

namespace ContinuousPipe\River\LogStream\ArchiveLogs\EventListener\TideFinished;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\LogStream\ArchiveLogs\Command\ArchiveTideCommand;
use ContinuousPipe\River\View\TideRepository;
use SimpleBus\Message\Bus\MessageBus;

class DelayArchiveLogs
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var int
     */
    private $delay;

    public function __construct(MessageBus $commandBus, TideRepository $tideRepository, int $delay)
    {
        $this->tideRepository = $tideRepository;
        $this->commandBus = $commandBus;
        $this->delay = $delay;
    }

    /**
     * @param TideEvent $event
     */
    public function notify(TideEvent $event)
    {
        $tide = $this->tideRepository->find($event->getTideUuid());

        $this->commandBus->handle(new ArchiveTideCommand(
            $tide->getUuid(),
            $tide->getLogId(),
            (new \DateTime())->add(new \DateInterval('PT'.$this->delay.'S'))
        ));
    }
}
