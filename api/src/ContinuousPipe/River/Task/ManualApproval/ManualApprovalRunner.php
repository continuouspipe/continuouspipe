<?php

namespace ContinuousPipe\River\Task\ManualApproval;

use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\TaskRunner;
use ContinuousPipe\River\Task\TaskRunnerException;
use ContinuousPipe\River\Tide;
use LogStream\LoggerFactory;

class ManualApprovalRunner implements TaskRunner
{
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Tide $tide, Task $task)
    {
        if (!$task instanceof ManualApprovalTask) {
            throw new TaskRunnerException('This runner only supports ManualApproval tasks', 0, null, $task);
        }

        return $task->start($tide, $this->loggerFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Task $task): bool
    {
        return $task instanceof ManualApprovalTask;
    }
}
