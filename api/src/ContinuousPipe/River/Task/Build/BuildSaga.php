<?php

namespace ContinuousPipe\River\Task\Build;

use ContinuousPipe\River\Task\Build\Command\ReceiveBuildNotification;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Transaction\TransactionManager;

class BuildSaga
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @param TransactionManager $transactionManager
     */
    public function __construct(TransactionManager $transactionManager)
    {
        $this->transactionManager = $transactionManager;
    }

    public function handle(ReceiveBuildNotification $command)
    {
        $this->transactionManager->apply($command->getTideUuid(), function (Tide $tide) use ($command) {
            /** @var BuildTask[] $tasks */
            $tasks = $tide->getTasks()->ofType(BuildTask::class);

            foreach ($tasks as $task) {
                $task->receiveBuildNotification($command->getBuild());
            }
        });
    }
}
