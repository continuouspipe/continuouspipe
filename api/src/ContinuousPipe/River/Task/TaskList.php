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
            $task->apply($event);
        }
    }

    /**
     * Has a task running ?
     *
     * @return bool
     */
    public function hasRunning()
    {
        foreach ($this->tasks as $task) {
            if ($task->isRunning()) {
                return true;
            }
        }

        return false;
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
    public function isSuccessful()
    {
        return array_reduce($this->tasks, function ($successful, Task $task) {
            return $successful && $task->isSuccessful();
        }, true);
    }
}
