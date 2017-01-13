<?php

namespace ContinuousPipe\River\Task\Build\Event;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\TaskEvent;
use LogStream\Log;
use Ramsey\Uuid\Uuid;

class ImageBuildsStarted implements TideEvent, TaskEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;
    /**
     * @var BuildRequest[]
     */
    private $buildRequests;
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
     * @param BuildRequest[] $buildRequests
     * @param Log $log
     * @param string $taskIdentifier
     */
    public function __construct(Uuid $tideUuid, string $taskIdentifier, array $buildRequests, Log $log)
    {
        $this->tideUuid = $tideUuid;
        $this->buildRequests = $buildRequests;
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
     * @return BuildRequest[]
     */
    public function getBuildRequests()
    {
        return $this->buildRequests;
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
