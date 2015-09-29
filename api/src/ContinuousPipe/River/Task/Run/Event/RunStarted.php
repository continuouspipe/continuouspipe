<?php

namespace ContinuousPipe\River\Task\Run\Event;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\TaskEvent;
use Rhumsaa\Uuid\Uuid;

class RunStarted implements TideEvent, TaskEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var int
     */
    private $taskId;

    /**
     * @var Uuid
     */
    private $runUuid;

    /**
     * @param Uuid $tideUuid
     * @param int  $taskId
     * @param Uuid $runUuid
     */
    public function __construct(Uuid $tideUuid, $taskId, Uuid $runUuid)
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
     * @return Uuid
     */
    public function getRunUuid()
    {
        return $this->runUuid;
    }
}
