<?php

namespace ContinuousPipe\River\Task\Wait\Event;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\TaskEvent;
use Rhumsaa\Uuid\Uuid;

class WaitEvent implements TideEvent, TaskEvent
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
     * @param Uuid   $tideUuid
     * @param string $taskId
     */
    public function __construct(Uuid $tideUuid, $taskId)
    {
        $this->tideUuid = $tideUuid;
        $this->taskId = $taskId;
    }

    /**
     *{@inheritdoc}
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
}
