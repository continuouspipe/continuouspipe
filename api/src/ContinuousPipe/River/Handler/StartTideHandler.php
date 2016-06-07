<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\View\TideRepository;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param MessageBus $eventBus
     * @param TideRepository $tideRepository
     * @param Tide\Concurrency\TideConcurrencyManager $concurrencyManager
     * @param LoggerInterface $logger
     */
    public function __construct(MessageBus $eventBus, TideRepository $tideRepository, Tide\Concurrency\TideConcurrencyManager $concurrencyManager, LoggerInterface $logger)
    {
        $this->eventBus = $eventBus;
        $this->tideRepository = $tideRepository;
        $this->concurrencyManager = $concurrencyManager;
        $this->logger = $logger;
    }

    /**
     * @param StartTideCommand $command
     */
    public function handle(StartTideCommand $command)
    {
        try {
            $tide = $this->tideRepository->find($command->getTideUuid());
        } catch (TideNotFound $e) {
            $this->logger->error('Tide not found, so not started', [
                'tideUuid' => (string) $command->getTideUuid(),
            ]);

            return;
        }

        if ($this->concurrencyManager->shouldTideStart($tide)) {
            $this->eventBus->handle(new TideStarted($command->getTideUuid()));
        } else {
            $this->concurrencyManager->postPoneTideStart($tide);
        }
    }
}
