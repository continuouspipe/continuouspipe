<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Concurrency\Command\RunPendingTidesCommand;
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
    /**
     * @var MessageBus
     */
    private $commandBus;
    /**
     * @var int
     */
    private $retryStartInterval;

    public function __construct(
        ViewTideRepository $viewTideRepository,
        Tide\Concurrency\TideConcurrencyManager $concurrencyManager,
        LoggerInterface $logger,
        Tide\Transaction\TransactionManager $transactionManager,
        LoggerFactory $loggerFactory,
        MessageBus $commandBus,
        int $retryStartInterval
    ) {
        $this->viewTideRepository = $viewTideRepository;
        $this->concurrencyManager = $concurrencyManager;
        $this->logger = $logger;
        $this->transactionManager = $transactionManager;
        $this->loggerFactory = $loggerFactory;
        $this->commandBus = $commandBus;
        $this->retryStartInterval = $retryStartInterval;
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

        $recommendation = $this->concurrencyManager->tideStartRecommendation($tide);

        if ($recommendation->shouldPostpone()) {
            $this->commandBus->handle(new RunPendingTidesCommand(
                $tide->getFlowUuid(),
                $tide->getCodeReference()->getBranch(),
                $recommendation->shouldPostponeTo()
            ));

            $this->logger->info('Decided to postpone tide start', [
                'reason' => $recommendation->reason(),
                'run_at' => $recommendation->shouldPostponeTo(),
                'tide_uuid' => $tide->getUuid()->toString(),
                'flow_uuid' => $tide->getFlowUuid()->toString()
            ]);

            return;
        }

        $this->startTide($command);
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
