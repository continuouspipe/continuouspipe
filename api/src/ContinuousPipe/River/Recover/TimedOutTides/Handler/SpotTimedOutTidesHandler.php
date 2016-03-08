<?php

namespace ContinuousPipe\River\Recover\TimedOutTides\Handler;

use ContinuousPipe\River\Recover\TimedOutTides\Command\SpotTimedOutTidesCommand;
use ContinuousPipe\River\Recover\TimedOutTides\Event\TideTimedOut;
use ContinuousPipe\River\Recover\TimedOutTides\TimedOutTideRepository;
use SimpleBus\Message\Bus\MessageBus;

class SpotTimedOutTidesHandler
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var TimedOutTideRepository
     */
    private $timedOutTideRepository;

    /**
     * @param TimedOutTideRepository $timedOutTideRepository
     * @param MessageBus             $eventBus
     */
    public function __construct(TimedOutTideRepository $timedOutTideRepository, MessageBus $eventBus)
    {
        $this->eventBus = $eventBus;
        $this->timedOutTideRepository = $timedOutTideRepository;
    }

    /**
     * @param SpotTimedOutTidesCommand $command
     */
    public function handle(SpotTimedOutTidesCommand $command)
    {
        $timedOutTides = $this->timedOutTideRepository->findByFlow($command->getFlowUuid());

        foreach ($timedOutTides as $tide) {
            $this->eventBus->handle(new TideTimedOut($tide->getUuid()));
        }
    }
}
