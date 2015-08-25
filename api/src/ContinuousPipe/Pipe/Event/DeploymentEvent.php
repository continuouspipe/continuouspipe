<?php

namespace ContinuousPipe\Pipe\Event;

use Rhumsaa\Uuid\Uuid;

interface DeploymentEvent
{
    /**
     * @return Uuid
     */
    public function getDeploymentUuid();
}
