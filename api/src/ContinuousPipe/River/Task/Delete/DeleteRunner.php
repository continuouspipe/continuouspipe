<?php

namespace ContinuousPipe\River\Task\Delete;

use ContinuousPipe\River\Environment\DeployedEnvironmentRepository;
use ContinuousPipe\River\Pipe\DeploymentRequest\Cluster\TargetClusterResolver;
use ContinuousPipe\River\Pipe\DeploymentRequest\EnvironmentName\EnvironmentNamingStrategy;
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

    /**
     * @var EnvironmentNamingStrategy
     */
    private $environmentNamingStrategy;

    /**
     * @var TargetClusterResolver
     */
    private $targetClusterResolver;

    public function __construct(
        LoggerFactory $loggerFactory,
        DeployedEnvironmentRepository $deployedEnvironmentRepository,
        EnvironmentNamingStrategy $environmentNamingStrategy,
        TargetClusterResolver $targetClusterResolver
    ) {
        $this->loggerFactory = $loggerFactory;
        $this->deployedEnvironmentRepository = $deployedEnvironmentRepository;
        $this->environmentNamingStrategy = $environmentNamingStrategy;
        $this->targetClusterResolver = $targetClusterResolver;
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
            $this->deployedEnvironmentRepository,
            $this->environmentNamingStrategy,
            $this->targetClusterResolver
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
