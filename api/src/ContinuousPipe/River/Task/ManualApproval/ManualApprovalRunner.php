<?php

namespace ContinuousPipe\River\Task\ManualApproval;

use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\TaskRunner;
use ContinuousPipe\River\Tide;
use LogStream\LoggerFactory;

class ManualApprovalRunner implements TaskRunner
{
    /**
     * @var TaskRunner
     */
    private $nextRunner;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param TaskRunner    $nextRunner
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(TaskRunner $nextRunner, LoggerFactory $loggerFactory)
    {
        $this->nextRunner = $nextRunner;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Tide $tide, Task $task)
    {
        if (!$task instanceof ManualApprovalTask) {
            return $this->nextRunner->run($tide, $task);
        }

        return $task->start($tide, $this->loggerFactory);
    }
}
