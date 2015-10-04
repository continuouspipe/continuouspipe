<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;
use Rhumsaa\Uuid\Uuid;

class TaskFailed implements TideEvent
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
     * @var \Exception
     */
    private $exception;

    /**
     * @param Uuid       $tideUuid
     * @param Task       $task
     * @param \Exception $exception
     */
    public function __construct(Uuid $tideUuid, Task $task, \Exception $exception)
    {
        $this->tideUuid = $tideUuid;
        $this->task = $task;
        $this->exception = $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
