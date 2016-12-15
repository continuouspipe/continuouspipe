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
     * @var string
     */
    private $taskId;

    /**
     * @var string
     */
    private $taskLogId;

    /**
     * @param Uuid   $tideUuid
     * @param string $taskId
     * @param string $taskLogId
     */
    public function __construct(Uuid $tideUuid, string $taskId, string $taskLogId)
    {
        $this->tideUuid = $tideUuid;
        $this->taskId = $taskId;
        $this->taskLogId = $taskLogId;
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
        return $this->taskId;
    }

    /**
     * @return string
     */
    public function getTaskLogId()
    {
        return $this->taskLogId;
    }
}
