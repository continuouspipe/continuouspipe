<?php

namespace ContinuousPipe\River\Task\Run\Event;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\TaskEvent;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class RunStarted implements TideEvent, TaskEvent
{
    /**
     * @var UuidInterface
     */
    private $tideUuid;

    /**
     * @var int
     */
    private $taskId;

    /**
     * @var UuidInterface
     */
    private $runUuid;

    /**
     * @param UuidInterface $tideUuid
     * @param int  $taskId
     * @param UuidInterface $runUuid
     */
    public function __construct(UuidInterface $tideUuid, $taskId, UuidInterface $runUuid)
    {
        $this->tideUuid = $tideUuid;
        $this->taskId = $taskId;
        $this->runUuid = $runUuid;
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
     * @return UuidInterface
     */
    public function getRunUuid()
    {
        return $this->runUuid;
    }
}
