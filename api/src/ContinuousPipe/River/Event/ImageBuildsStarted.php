<?php

namespace ContinuousPipe\River\Event;

use ContinuousPipe\Builder\Request\BuildRequest;
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
     * @param Uuid           $tideUuid
     * @param BuildRequest[] $buildRequests
     */
    public function __construct(Uuid $tideUuid, array $buildRequests)
    {
        $this->tideUuid = $tideUuid;
        $this->buildRequests = $buildRequests;
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
}
