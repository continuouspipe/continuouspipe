<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;
use Rhumsaa\Uuid\Uuid;

class TaskSkipped implements TideEvent, TaskEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var Task
     */
    private $task;

    /**
     * @param Uuid $tideUuid
     * @param Task $task
     */
    public function __construct(Uuid $tideUuid, Task $task)
    {
        $this->tideUuid = $tideUuid;
        $this->task = $task;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaskId()
    {
        return $this->task->getContext()->getTaskId();
    }

    /**
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }
}
