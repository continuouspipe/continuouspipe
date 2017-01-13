<?php

namespace ContinuousPipe\River\Task\Build\Event;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\TaskEvent;
use LogStream\Log;
use Ramsey\Uuid\Uuid;

class ImageBuildsFailed implements TideEvent, TaskEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;
    /**
     * @var Log
     */
    private $log;
    /**
     * @var string
     */
    private $taskIdentifier;

    /**
     * @param Uuid $tideUuid
     * @param string $taskIdentifier
     * @param Log $log
     */
    public function __construct(Uuid $tideUuid, string $taskIdentifier, Log $log)
    {
        $this->tideUuid = $tideUuid;
        $this->log = $log;
        $this->taskIdentifier = $taskIdentifier;
    }

    /**
     * @return Uuid
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return Log
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaskId()
    {
        return $this->taskIdentifier;
    }
}
