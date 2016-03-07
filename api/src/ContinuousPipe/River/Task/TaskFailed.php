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
    private $taskContext;

    /**
     * @var string
     */
    private $message;

    /**
     * @param Uuid       $tideUuid
     * @param TaskContext $taskContext
     * @param string $message
     */
    public function __construct(Uuid $tideUuid, TaskContext $taskContext, $message)
    {
        $this->tideUuid = $tideUuid;
        $this->taskContext = $taskContext;
        $this->message = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return TaskContext
     */
    public function getTaskContext()
    {
        return $this->taskContext;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
