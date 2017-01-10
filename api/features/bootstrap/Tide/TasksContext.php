<?php

namespace Tide;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\River\ContextualizedTask;
use ContinuousPipe\River\EventCollection;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Task\Build\BuildTask;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsFailed;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsSuccessful;
use ContinuousPipe\River\Task\Deploy\DeployTask;
use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\Task\Run\Event\RunStarted;
use ContinuousPipe\River\Task\Run\RunTask;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Tide;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
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
        if ($this->getTasksOfType(BuildTask::class)[0]->getStatus() !== Task::STATUS_RUNNING) {
            throw new \RuntimeException('The build task is not running');
        }
    }

    /**
     * @Then the deploy task should not be started
     */
    public function theDeployTaskShouldNotBeStarted()
    {
        $deployTasks = $this->getTasksOfType(DeployTask::class);
        if (count($deployTasks) === 0) {
            throw new \RuntimeException('Did not found any deploy task');
        }

        if ($deployTasks[0]->getStatus() == Task::STATUS_RUNNING) {
            throw new \RuntimeException('The deploy task is running');
        }
    }

    /**
     * @Then the build task of the first tide should be skipped
     */
    public function theBuildTaskOfTheFirstTideShouldBeSkipped()
    {
        $tide = $this->tideContext->findTideByIndex(0);
        $task = $this->getTasksOfType(BuildTask::class, $tide->getUuid())[0];

        if ($task->getStatus() != Task::STATUS_SKIPPED) {
            throw new \RuntimeException('The build task is not skipped');
        }
    }

    /**
     * @Then the build task of the second tide should be running
     */
    public function theBuildTaskOfTheSecondTideShouldBeRunning()
    {
        $tide = $this->tideContext->findTideByIndex(1);
        $task = $this->getTasksOfType(BuildTask::class, $tide->getUuid())[0];

        if ($task->getStatus() !== Task::STATUS_RUNNING) {
            throw new \RuntimeException('The build task is not running');
        }
    }

    /**
     * @Then the deploy task should be started
     */
    public function theDeployTaskShouldBeStarted()
    {
        $deployTasks = $this->getTasksOfType(DeployTask::class);
        if (count($deployTasks) === 0) {
            throw new \RuntimeException('Did not found any deploy task');
        }

        if ($deployTasks[0]->getStatus() != Task::STATUS_RUNNING) {
            throw new \RuntimeException('The deploy task is not running');
        }
    }
    /**
     * @When the build task should not be running
     */
    public function theBuildTaskShouldNotBeRunning()
    {
        if ($this->getTasksOfType(BuildTask::class)[0]->getStatus() == Task::STATUS_RUNNING) {
            throw new \RuntimeException('The build task is running');
        }
    }

    /**
     * @Then the second run task should be running
     */
    public function theSecondRunTaskShouldBeRunning()
    {
        $task = $this->getTasksOfType(RunTask::class)[1];

        if ($task->getStatus() != Task::STATUS_RUNNING) {
            throw new \RuntimeException(sprintf(
                'The second run task is not running (%s)',
                $task->getStatus()
            ));
        }
    }

    /**
     * @Then the second deploy task should be running
     */
    public function theSecondDeployTaskShouldBeRunning()
    {
        $task = $this->getTasksOfType(DeployTask::class)[1];
        if ($task->getStatus() != Task::STATUS_RUNNING) {
            throw new \RuntimeException(sprintf(
                'The second deploy task is not running (%s)',
                $task->getStatus()
            ));
        }
    }

    /**
     * @Then the run task should be running
     */
    public function theRunTaskShouldBeRunning()
    {
        $task = $this->getTasksOfType(RunTask::class)[0];

        if ($task->getStatus() != Task::STATUS_RUNNING)  {
            throw new \RuntimeException(sprintf(
                'The run task is not running (%s)',
                $task->getStatus()
            ));
        }
    }

    /**
     * @Then the second deploy task should be pending
     */
    public function theSecondDeployTaskShouldBePending()
    {
        $task = $this->getTasksOfType(DeployTask::class)[1];

        if ($task->getStatus() !== Task::STATUS_PENDING) {
            throw new \RuntimeException(sprintf(
                'The second run task is not running (%s)',
                $task->getStatus()
            ));
        }
    }

    /**
     * @Then the tide should have the task :identifier
     */
    public function theTideShouldHaveTheTask($identifier)
    {
        $this->getCurrentTide()->getTask($identifier);
    }

    /**
     * @Then the tide should not have the task :arg1
     */
    public function theTideShouldNotHaveTheTask($identifier)
    {
        try {
            $this->getCurrentTide()->getTask($identifier);
            $found = true;
        } catch (\InvalidArgumentException $e) {
            $found = false;
        }

        if ($found) {
            throw new \RuntimeException('Task was found');
        }
    }

    /**
     * @Then the task named :name should be successful
     */
    public function theTaskNamedShouldBeSuccessful($name)
    {
        $this->assertTaskStatus($name, Task::STATUS_SUCCESSFUL);
    }

    /**
     * @Then the task named :name should be cancelled
     */
    public function theTaskNamedShouldBeCancelled($name)
    {
        $this->assertTaskStatus($name, Task::STATUS_CANCELLED);
    }

    /**
     * @Then the task named :name should be pending
     */
    public function theTaskNamedShouldBePending($name)
    {
        $this->assertTaskStatus($name, Task::STATUS_PENDING);
    }

    /**
     * @Then the task named :name should be running
     */
    public function theTaskNamedShouldBeRunning($name)
    {
        $this->assertTaskStatus($name, Task::STATUS_RUNNING);
    }

    /**
     * @Then the task named :name should be skipped
     */
    public function theTaskNamedShouldBeSkipped($name)
    {
        $this->assertTaskStatus($name, Task::STATUS_SKIPPED);
    }

    private function assertTaskStatus(string $taskName, string $status)
    {
        $task = $this->getTask($taskName);
        if ($task->getStatus() != $status) {
            throw new \RuntimeException(sprintf(
                'Expected status "%s" but found "%s"',
                $status,
                $task->getStatus()
            ));
        }
    }


    /**
     * @param string $taskType
     * @param Uuid $tideUuid
     *
     * @return \ContinuousPipe\River\Task\Task[]
     */
    public function getTasksOfType($taskType, Uuid $tideUuid = null)
    {
        $tide = null === $tideUuid ? $this->getCurrentTide() : $this->tideRepository->find($tideUuid);
        $tasks = $tide->getTasks()->getTasks();

        return array_values(array_filter($tasks, function (Task $task) use ($taskType) {
            return get_class($task) == $taskType || is_subclass_of($task, $taskType);
        }));
    }

    /**
     * @return \ContinuousPipe\River\Tide
     */
    private function getCurrentTide()
    {
        if (null === ($uuid = $this->tideContext->getCurrentTideUuid())) {
            throw new \RuntimeException('No running tide found');
        }

        return $this->tideRepository->find($uuid);
    }

    /**
     * @param EventDrivenTask $task
     *
     * @return EventCollection
     */
    public function getTaskEvents(EventDrivenTask $task)
    {
        $reflection = new \ReflectionObject($task);
        $property = $reflection->getProperty('events');
        $property->setAccessible(true);

        return $property->getValue($task);
    }

    /**
     * @param string $taskName
     * @param UuidInterface $tideUuid
     *
     * @return Task
     */
    private function getTask(string $taskName, UuidInterface $tideUuid = null): Task
    {
        $tide = null === $tideUuid ? $this->getCurrentTide() : $this->tideRepository->find($tideUuid);
        $tasks = $tide->getTasks()->getTasks();
        foreach ($tasks as $task) {
            if ($task->getIdentifier() == $taskName) {
                return $task;
            }
        }

        throw new \RuntimeException(sprintf(
            'Task named "%s" not found',
            $taskName
        ));
    }
}
