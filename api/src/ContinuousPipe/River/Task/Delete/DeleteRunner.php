<?php

namespace ContinuousPipe\River\Task\Delete;

use ContinuousPipe\River\Environment\DeployedEnvironmentRepository;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\TaskRunner;
use ContinuousPipe\River\Task\TaskRunnerException;
use ContinuousPipe\River\Tide;
use LogStream\LoggerFactory;

class DeleteRunner implements TaskRunner
{
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var DeployedEnvironmentRepository
     */
    private $deployedEnvironmentRepository;

    public function __construct(LoggerFactory $loggerFactory, DeployedEnvironmentRepository $deployedEnvironmentRepository)
    {
        $this->loggerFactory = $loggerFactory;
        $this->deployedEnvironmentRepository = $deployedEnvironmentRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Tide $tide, Task $task)
    {
        if (!$task instanceof DeleteTask) {
            throw new TaskRunnerException(sprintf('Cannot run a task of type "%s"', get_class($task)), 0);
        }

        return $task->start(
            $tide,
            $this->loggerFactory,
            $this->deployedEnvironmentRepository
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Task $task): bool
    {
        return $task instanceof DeleteTask;
    }
}
