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
     * @var Tide\Transaction\TransactionManager
     */
    private $transactionManager;

    /**
     * @param ViewTideRepository $viewTideRepository
     * @param Tide\Concurrency\TideConcurrencyManager $concurrencyManager
     * @param LoggerInterface $logger
     * @param Tide\Transaction\TransactionManager $transactionManager
     */
    public function __construct(
        ViewTideRepository $viewTideRepository,
        Tide\Concurrency\TideConcurrencyManager $concurrencyManager,
        LoggerInterface $logger,
        Tide\Transaction\TransactionManager $transactionManager
    ) {
        $this->viewTideRepository = $viewTideRepository;
        $this->concurrencyManager = $concurrencyManager;
        $this->logger = $logger;
        $this->transactionManager = $transactionManager;
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
        $this->transactionManager->apply($command->getTideUuid(), function (Tide $tide) {
            $tide->start();
        });
    }
}
