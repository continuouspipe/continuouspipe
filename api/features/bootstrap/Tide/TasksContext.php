<?php

namespace Tide;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\River\ContextualizedTask;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Task\Build\BuildTask;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsFailed;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsSuccessful;
use ContinuousPipe\River\Task\Deploy\DeployTask;
use ContinuousPipe\River\Task\Run\RunTask;
use ContinuousPipe\River\Task\Task;
use SimpleBus\Message\Bus\MessageBus;

class TasksContext implements Context
{
    /**
     * @var \TideContext
     */
    private $tideContext;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param MessageBus     $eventBus
     * @param TideRepository $tideRepository
     */
    public function __construct(MessageBus $eventBus, TideRepository $tideRepository)
    {
        $this->eventBus = $eventBus;
        $this->tideRepository = $tideRepository;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->tideContext = $scope->getEnvironment()->getContext('TideContext');
    }

    /**
     * @Then the build task should be started
     */
    public function theBuildTaskShouldBeStarted()
    {
        if (!$this->getTasksOfType(BuildTask::class)[0]->isRunning()) {
            throw new \RuntimeException('The build task is not running');
        }
    }

    /**
     * @Then the deploy task should not be started
     */
    public function theDeployTaskShouldNotBeStarted()
    {
        if ($this->getTasksOfType(DeployTask::class)[0]->isRunning()) {
            throw new \RuntimeException('The deploy task is running');
        }
    }

    /**
     * @Then the deploy task should be started
     */
    public function theDeployTaskShouldBeStarted()
    {
        if (!$this->getTasksOfType(DeployTask::class)[0]->isRunning()) {
            throw new \RuntimeException('The deploy task is not running');
        }
    }
    /**
     * @When the build task should not be running
     */
    public function theBuildTaskShouldNotBeRunning()
    {
        if ($this->getTasksOfType(BuildTask::class)[0]->isRunning()) {
            throw new \RuntimeException('The build task is running');
        }
    }

    /**
     * @Then the second run task should be running
     */
    public function theSecondRunTaskShouldBeRunning()
    {
        $task = $this->getTasksOfType(RunTask::class)[1];

        if (!$task->isRunning()) {
            throw new \RuntimeException(sprintf(
                'The second run task is not running (successful=%b failed=%b pending=%b)',
                $task->isSuccessful(),
                $task->isFailed(),
                $task->isPending()
            ));
        }
    }

    /**
     * @When the build task succeed
     */
    public function theBuildTaskSucceed()
    {
        $tide = $this->getCurrentTide();

        $this->eventBus->handle(new ImageBuildsSuccessful(
            $tide->getUuid(),
            $tide->getContext()->getLog()
        ));
    }

    /**
     * @When the build task failed
     */
    public function theBuildTaskFailed()
    {
        $tide = $this->getCurrentTide();

        $this->eventBus->handle(new ImageBuildsFailed(
            $tide->getUuid(),
            $tide->getContext()->getLog()
        ));
    }

    /**
     * @param string $taskType
     *
     * @return \ContinuousPipe\River\Task\Task[]
     */
    public function getTasksOfType($taskType)
    {
        $tasks = $this->getCurrentTide()->getTasks()->getTasks();

        return array_values(array_filter($tasks, function (Task $task) use ($taskType) {
            if ($task instanceof ContextualizedTask) {
                $task = $task->getTask();
            }

            return get_class($task) == $taskType || is_subclass_of($task, $taskType);
        }));
    }

    /**
     * @return \ContinuousPipe\River\Tide
     */
    private function getCurrentTide()
    {
        return $this->tideRepository->find(
            $this->tideContext->getCurrentTideUuid()
        );
    }
}
