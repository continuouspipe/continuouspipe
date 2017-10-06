<?php

namespace ContinuousPipe\River\Task\Run;

use ContinuousPipe\Pipe\Client\Client;
use ContinuousPipe\River\Task\Run\RunRequest\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\TaskRunner;
use ContinuousPipe\River\Task\TaskRunnerException;
use ContinuousPipe\River\Tide;

class RunTaskRunner implements TaskRunner
{
    /**
     * @var DeploymentRequestFactory
     */
    private $deploymentRequestFactory;

    /**
     * @var Client
     */
    private $pipeClient;

    /**
     * @param DeploymentRequestFactory $deploymentRequestFactory
     * @param Client                   $pipeClient
     */
    public function __construct(DeploymentRequestFactory $deploymentRequestFactory, Client $pipeClient)
    {
        $this->deploymentRequestFactory = $deploymentRequestFactory;
        $this->pipeClient = $pipeClient;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Tide $tide, Task $task)
    {
        if (!$task instanceof RunTask) {
            throw new TaskRunnerException('This runner only runs the deploy tasks', 0, null, $task);
        }

        $task->run($tide, $this->deploymentRequestFactory, $this->pipeClient);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Task $task): bool
    {
        return $task instanceof RunTask;
    }
}
