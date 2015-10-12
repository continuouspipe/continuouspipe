<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;
use LogStream\Log;
use Rhumsaa\Uuid\Uuid;

class TaskLogCreated implements TideEvent, TaskEvent
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
     * @var Log
     */
    private $log;

    /**
     * @param Uuid   $tideUuid
     * @param string $taskId
     * @param Log    $log
     */
    public function __construct(Uuid $tideUuid, $taskId, Log $log)
    {
        $this->tideUuid = $tideUuid;
        $this->taskId = $taskId;
        $this->log = $log;
    }

    /**
     * @param TaskContext $context
     *
     * @return TaskLogCreated
     */
    public static function fromContext(TaskContext $context)
    {
        return new static(
            $context->getTideUuid(),
            $context->getTaskId(),
            $context->getTaskLog()
        );
    }

    /**
     * @return Uuid
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return string
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * @return Log
     */
    public function getLog()
    {
        return $this->log;
    }
}
