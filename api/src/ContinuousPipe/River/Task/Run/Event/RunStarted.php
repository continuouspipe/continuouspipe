<?php

namespace ContinuousPipe\River\Task\Run\Event;

use ContinuousPipe\River\Event\TideEvent;
use Rhumsaa\Uuid\Uuid;

class RunStarted implements TideEvent, RunEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var Uuid
     */
    private $runUuid;

    /**
     * @var int
     */
    private $taskId;

    /**
     * @param Uuid $tideUuid
     * @param Uuid $runUuid
     * @param int $taskId
     */
    public function __construct(Uuid $tideUuid, Uuid $runUuid, $taskId)
    {
        $this->tideUuid = $tideUuid;
        $this->runUuid = $runUuid;
        $this->taskId = $taskId;
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
    public function getRunUuid()
    {
        return $this->runUuid;
    }

    /**
     * @return int
     */
    public function getTaskId()
    {
        return $this->taskId;
    }
}
