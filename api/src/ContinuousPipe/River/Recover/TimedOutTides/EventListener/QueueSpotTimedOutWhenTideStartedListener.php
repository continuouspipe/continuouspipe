<?php

namespace ContinuousPipe\River\Recover\TimedOutTides\EventListener;

use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Recover\TimedOutTides\Command\SpotTimedOutTidesCommand;
use ContinuousPipe\River\View\TideRepository;
use SimpleBus\Message\Bus\MessageBus;

class QueueSpotTimedOutWhenTideStartedListener
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var int
     */
    private $tideTimeout;

    /**
     * @param MessageBus $commandBus
     * @param TideRepository    $tideRepository
     * @param int               $tideTimeout
     */
    public function __construct(MessageBus $commandBus, TideRepository $tideRepository, $tideTimeout)
    {
        $this->commandBus = $commandBus;
        $this->tideRepository = $tideRepository;
        $this->tideTimeout = $tideTimeout;
    }

    /**
     * @param TideStarted $event
     */
    public function notify(TideStarted $event)
    {
        $tide = $this->tideRepository->find($event->getTideUuid());

        $this->commandBus->handle(new SpotTimedOutTidesCommand(
            $tide->getFlowUuid(),
            (new \DateTime())->add(new \DateInterval('PT'.$this->tideTimeout.'S'))
        ));
    }
}
