<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\TideContext;

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
     * @param TideContext $context
     * @param TideEvent $event
     */
    public function apply(TideContext $context, TideEvent $event)
    {
        foreach ($this->tasks as $task) {
            $task->apply($context, $event);
        }
    }

    /**
     * Has a running task ?
     *
     * @return bool
     */
    public function hasRunning()
    {
        return 0 < count(array_filter($this->tasks, function (Task $task) {
            return $task->isRunning();
        }));
    }

    /**
     * Has a failed task ?
     *
     * @return bool
     */
    public function hasFailed()
    {
        return 0 < count(array_filter($this->tasks, function (Task $task) {
            return $task->isFailed();
        }));
    }

    /**
     * @return Task|null
     */
    public function next()
    {
        foreach ($this->tasks as $task) {
            if ($task->isPending()) {
                return $task;
            }
        }

        return;
    }

    /**
     * @return bool
     */
    public function allSuccessful()
    {
        return array_reduce($this->tasks, function ($successful, Task $task) {
            return $successful && $task->isSuccessful();
        }, true);
    }

    /**
     * @param $event
     */
    public function removeEventIfAlreadyInCollection($event)
    {
        foreach ($this->tasks as $task) {
            $task->getEvents()->removeIfExists($event);
        }
    }
}
