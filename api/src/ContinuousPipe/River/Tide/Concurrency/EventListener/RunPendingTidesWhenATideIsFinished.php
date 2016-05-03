<?php

namespace ContinuousPipe\River\Tide\Concurrency\EventListener;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Tide\Concurrency\Command\RunPendingTidesCommand;
use ContinuousPipe\River\View\TideRepository;
use SimpleBus\Message\Bus\MessageBus;

class RunPendingTidesWhenATideIsFinished
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
     * @param TideRepository $tideRepository
     * @param MessageBus $commandBus
     */
    public function __construct(TideRepository $tideRepository, MessageBus $commandBus)
    {
        $this->tideRepository = $tideRepository;
        $this->commandBus = $commandBus;
    }

    /**
     * @param TideEvent $tideEvent
     */
    public function notify(TideEvent $tideEvent)
    {
        $tide = $this->tideRepository->find($tideEvent->getTideUuid());

        $this->commandBus->handle(new RunPendingTidesCommand(
            $tide->getFlow()->getUuid(),
            $tide->getCodeReference()->getBranch()
        ));
    }
}
