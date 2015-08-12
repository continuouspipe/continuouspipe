<?php

namespace ContinuousPipe\River\Task\Deploy\Event;

use ContinuousPipe\River\Event\TideEvent;
use Rhumsaa\Uuid\Uuid;

class DeploymentEvent implements TideEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @param Uuid $tideUuid
     */
    public function __construct(Uuid $tideUuid)
    {
        $this->tideUuid = $tideUuid;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }
}
