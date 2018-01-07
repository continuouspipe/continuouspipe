<?php

namespace ContinuousPipe\River\Task\Wait;

use ContinuousPipe\River\Event\GitHub\StatusUpdated;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Transaction\TransactionManager;

class WaitSaga
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

    public function notify(StatusUpdated $statusUpdated)
    {
        $this->transactionManager->apply($statusUpdated->getTideUuid(), function (Tide $tide) use ($statusUpdated) {
            /** @var WaitTask[] $tasks */
            $tasks = $tide->getTasks()->ofType(WaitTask::class);

            foreach ($tasks as $task) {
                $task->statusUpdated($statusUpdated);
            }
        });
    }
}
