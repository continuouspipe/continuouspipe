<?php

namespace ContinuousPipe\River\Task\Run\Event;

use ContinuousPipe\River\Event\TideEvent;
use Rhumsaa\Uuid\Uuid;

class RunStarted implements TideEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var Uuid
     */
    private $buildUuid;

    /**
     * @param Uuid $tideUuid
     * @param Uuid $buildUuid
     */
    public function __construct(Uuid $tideUuid, Uuid $buildUuid)
    {
        $this->tideUuid = $tideUuid;
        $this->buildUuid = $buildUuid;
    }
    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return Uuid
     */
    public function getBuildUuid()
    {
        return $this->buildUuid;
    }
}
