<?php

namespace ContinuousPipe\River\Task\ManualApproval\Handler;

use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Task\ManualApproval\Command\Approve;
use ContinuousPipe\River\Task\ManualApproval\Command\ManualApprovalCommand;
use ContinuousPipe\River\Task\ManualApproval\Command\Reject;
use ContinuousPipe\River\Task\ManualApproval\ManualApprovalTask;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Transaction\TransactionManager;
use LogStream\LoggerFactory;
use SimpleBus\Message\Bus\MessageBus;

class ChoiceHandler
{
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @param TransactionManager $transactionManager
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(TransactionManager $transactionManager, LoggerFactory $loggerFactory)
    {
        $this->loggerFactory = $loggerFactory;
        $this->transactionManager = $transactionManager;
    }

    public function handle(ManualApprovalCommand $command)
    {
        $this->transactionManager->apply($command->getTideUuid(), function (Tide $tide) use ($command) {
            $task = $tide->getTask($command->getTaskIdentifier());

            if (!$task instanceof ManualApprovalTask) {
                throw new \InvalidArgumentException(sprintf('The task "%s" is not a manual approval task', $command->getTaskIdentifier()));
            }

            if ($command instanceof Approve) {
                $task->approve($this->loggerFactory, $command->getUser());
            } elseif ($command instanceof Reject) {
                $task->reject($this->loggerFactory, $command->getUser());
            }
        });
    }
}
