<?php

namespace Task;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\Wait\WaitTask;

class WaitContext implements Context
{
    /**
     * @var \Tide\TasksContext
     */
    private $tideTasksContext;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->tideTasksContext = $scope->getEnvironment()->getContext('Tide\TasksContext');
    }

    /**
     * @Then the wait task should be failed
     */
    public function theWaitTaskShouldBeFailed()
    {
        if ($this->getWaitTask()->isFailed() != Task::STATUS_FAILED) {
            throw new \RuntimeException('Expected the task to be failed, be it\'s not');
        }
    }

    /**
     * @Then the wait task should be successful
     */
    public function theWaitTaskShouldBeSuccessful()
    {
        if ($this->getWaitTask()->getStatus() != Task::STATUS_SUCCESSFUL) {
            throw new \RuntimeException('Expected the task to be successful, be it\'s not');
        }
    }

    /**
     * @Then the wait task should be running
     */
    public function theWaitTaskShouldBeRunning()
    {
        if ($this->getWaitTask()->getStatus() !== Task::STATUS_RUNNING) {
            throw new \RuntimeException('Expected the task to be running, be it\'s not');
        }
    }

    /**
     * @return WaitTask
     */
    private function getWaitTask()
    {
        /* @var WaitTask[] $waitTasks */
        $waitTasks = $this->tideTasksContext->getTasksOfType(WaitTask::class);

        if (count($waitTasks) == 0) {
            throw new \RuntimeException('No wait task found');
        }

        return current($waitTasks);
    }
}
