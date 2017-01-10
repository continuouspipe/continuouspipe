<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;

class TaskList
{
    /**
     * @var Task[]
     */
    private $tasks;

    /**
     * @param Task[] $tasks
     */
    public function __construct(array $tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * @return Task[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Apply an event on all the tasks.
     *
     * @param TideEvent $event
     */
    public function apply(TideEvent $event)
    {
        foreach ($this->tasks as $task) {
            if ($task->accept($event)) {
                $task->apply($event);
            }
        }
    }

    /**
     * Has a running task ?
     *
     * @return bool
     */
    public function hasRunning()
    {
        foreach ($this->tasks as $task) {
            if ($task->getStatus() == Task::STATUS_RUNNING) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get failed task.
     *
     * If no failed task, then returns NULL.
     *
     * @return Task|null
     */
    public function getFailedTask()
    {
        foreach ($this->tasks as $task) {
            if ($task->getStatus() == Task::STATUS_FAILED) {
                return $task;
            }
        }
    }

    /**
     * @return Task|null
     */
    public function next()
    {
        foreach ($this->tasks as $task) {
            if ($task->getStatus() == Task::STATUS_PENDING) {
                return $task;
            }
        }
    }

    /**
     * @return bool
     */
    public function allSuccessful()
    {
        return array_reduce($this->tasks, function ($successful, Task $task) {
            return $successful && in_array($task->getStatus(), [Task::STATUS_SUCCESSFUL, Task::STATUS_SKIPPED]);
        }, true);
    }

    /**
     * @return Task|null
     */
    public function getCurrentTask()
    {
        foreach ($this->tasks as $task) {
            if ($task->getStatus() == Task::STATUS_RUNNING) {
                return $task;
            }
        }

        return;
    }

    /**
     * @param string $taskType
     *
     * @return Task[]
     */
    public function ofType(string $taskType) : array
    {
        $matchingTasks = [];

        foreach ($this->tasks as $task) {
            if (get_class($task) == $taskType) {
                $matchingTasks[] = $task;
            }
        }

        return $matchingTasks;
    }
}
