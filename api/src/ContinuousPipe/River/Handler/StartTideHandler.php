<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideConfigurationException;
use ContinuousPipe\River\View\TideRepository as ViewTideRepository;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
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
     * @var LoggerFactory
     */
    private $loggerFactory;

    public function __construct(
        ViewTideRepository $viewTideRepository,
        Tide\Concurrency\TideConcurrencyManager $concurrencyManager,
        LoggerInterface $logger,
        Tide\Transaction\TransactionManager $transactionManager,
        LoggerFactory $loggerFactory
    ) {
        $this->viewTideRepository = $viewTideRepository;
        $this->concurrencyManager = $concurrencyManager;
        $this->logger = $logger;
        $this->transactionManager = $transactionManager;
        $this->loggerFactory = $loggerFactory;
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
            try {
                $tide->start();
            } catch (TideConfigurationException $e) {
                $logger = $this->loggerFactory->from($tide->getLog());
                $logger->child(new Text($e->getMessage()))->updateStatus(Log::FAILURE);

                $tide->hasFailed($e);
            }
        });
    }
}
