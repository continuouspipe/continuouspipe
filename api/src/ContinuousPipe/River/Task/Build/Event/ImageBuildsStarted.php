<?php

namespace ContinuousPipe\River\Task\Build\Event;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\River\Event\TideEvent;
use LogStream\Log;
use Rhumsaa\Uuid\Uuid;

class ImageBuildsStarted implements TideEvent
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
     * @param Uuid           $tideUuid
     * @param BuildRequest[] $buildRequests
     * @param Log            $log
     */
    public function __construct(Uuid $tideUuid, array $buildRequests, Log $log)
    {
        $this->tideUuid = $tideUuid;
        $this->buildRequests = $buildRequests;
        $this->log = $log;
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
}
