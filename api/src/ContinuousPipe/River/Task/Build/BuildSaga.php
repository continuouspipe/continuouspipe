<?php

namespace ContinuousPipe\River\Task\Build;

use ContinuousPipe\Builder\Client\BuilderClient;
use ContinuousPipe\River\Task\Build\Command\ReceiveBuildNotification;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsStarted;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Transaction\TransactionManager;

class BuildSaga
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @var BuilderClient
     */
    private $builderClient;

    /**
     * @param TransactionManager $transactionManager
     * @param BuilderClient $builderClient
     */
    public function __construct(TransactionManager $transactionManager, BuilderClient $builderClient)
    {
        $this->transactionManager = $transactionManager;
        $this->builderClient = $builderClient;
    }

    public function handle($commandOrEvent)
    {
        if ($commandOrEvent instanceof ImageBuildsStarted) {
            $event = $commandOrEvent;
            $this->transactionManager->apply($event->getTideUuid(), function (Tide $tide) use ($event) {
                /** @var BuildTask $task */
                $task = $tide->getTask($event->getTaskId());

                foreach ($event->getBuildRequests() as $request) {
                    $task->build($this->builderClient, $request);
                }
            });
        }

        if ($commandOrEvent instanceof ReceiveBuildNotification) {
            $command = $commandOrEvent;
            $this->transactionManager->apply($command->getTideUuid(), function (Tide $tide) use ($command) {
                /** @var BuildTask[] $tasks */
                $tasks = $tide->getTasks()->ofType(BuildTask::class);

                foreach ($tasks as $task) {
                    $task->receiveBuildNotification($command->getBuild());
                }
            });
        }
    }
}
