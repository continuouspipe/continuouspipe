<?php

namespace ContinuousPipe\River\Task\Build;

use ContinuousPipe\Builder\Client\BuilderClient;
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

    public function notify($event)
    {
        if ($event instanceof ImageBuildsStarted) {
            $this->transactionManager->apply($event->getTideUuid(), function (Tide $tide) use ($event) {
                /** @var BuildTask $task */
                $task = $tide->getTask($event->getTaskId());

                foreach ($event->getBuildRequests() as $request) {
                    $task->build($this->builderClient, $request);
                }
            });
        }
    }
}
