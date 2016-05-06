<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\View\TideRepository;
use SimpleBus\Message\Bus\MessageBus;

class StartTideHandler
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var Tide\Concurrency\TideConcurrencyManager
     */
    private $concurrencyManager;

    /**
     * @param MessageBus                              $eventBus
     * @param TideRepository                          $tideRepository
     * @param Tide\Concurrency\TideConcurrencyManager $concurrencyManager
     */
    public function __construct(MessageBus $eventBus, TideRepository $tideRepository, Tide\Concurrency\TideConcurrencyManager $concurrencyManager)
    {
        $this->eventBus = $eventBus;
        $this->tideRepository = $tideRepository;
        $this->concurrencyManager = $concurrencyManager;
    }

    /**
     * @param StartTideCommand $command
     */
    public function handle(StartTideCommand $command)
    {
        $tide = $this->tideRepository->find($command->getTideUuid());

        if ($this->concurrencyManager->shouldTideStart($tide)) {
            $this->eventBus->handle(new TideStarted($command->getTideUuid()));
        } else {
            $this->concurrencyManager->postPoneTideStart($tide);
        }
    }
}
