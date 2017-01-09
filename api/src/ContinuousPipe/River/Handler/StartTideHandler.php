<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\View\TideRepository as ViewTideRepository;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;

class StartTideHandler
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var ViewTideRepository
     */
    private $viewTideRepository;

    /**
     * @var Tide\Concurrency\TideConcurrencyManager
     */
    private $concurrencyManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param MessageBus                              $eventBus
     * @param ViewTideRepository                      $viewTideRepository
     * @param Tide\Concurrency\TideConcurrencyManager $concurrencyManager
     * @param LoggerInterface                         $logger
     * @param TideRepository                          $tideRepository
     */
    public function __construct(
        MessageBus $eventBus,
        ViewTideRepository $viewTideRepository,
        Tide\Concurrency\TideConcurrencyManager $concurrencyManager,
        LoggerInterface $logger,
        TideRepository $tideRepository
    ) {
        $this->eventBus = $eventBus;
        $this->viewTideRepository = $viewTideRepository;
        $this->concurrencyManager = $concurrencyManager;
        $this->logger = $logger;
        $this->tideRepository = $tideRepository;
    }

    /**
     * @param StartTideCommand $command
     */
    public function handle(StartTideCommand $command)
    {
        try {
            $tide = $this->viewTideRepository->find($command->getTideUuid());
        } catch (TideNotFound $e) {
            $this->logger->error('Tide not found, so not started', [
                'tideUuid' => (string) $command->getTideUuid(),
            ]);

            return;
        }

        if ($this->concurrencyManager->shouldTideStart($tide)) {
            $this->startTide($command);
        } else {
            $this->concurrencyManager->postPoneTideStart($tide);
        }
    }

    private function startTide(StartTideCommand $command)
    {
        $tide = $this->tideRepository->find($command->getTideUuid());
        $tide->start();

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }
    }
}
