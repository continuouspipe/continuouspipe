<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;
use Ramsey\Uuid\Uuid;

class TaskSkipped implements TideEvent, TaskEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var TaskContext
     */
    private $taskContext;

    /**
     * @param Uuid        $tideUuid
     * @param TaskContext $taskContext
     */
    public function __construct(Uuid $tideUuid, TaskContext $taskContext)
    {
        $this->tideUuid = $tideUuid;
        $this->taskContext = $taskContext;
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
        return $this->taskContext->getTaskId();
    }

    /**
     * @return TaskContext
     */
    public function getTaskContext()
    {
        return $this->taskContext;
    }
}
