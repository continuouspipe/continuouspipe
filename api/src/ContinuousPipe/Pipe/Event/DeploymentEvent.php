<?php

namespace ContinuousPipe\Pipe\Event;

use Ramsey\Uuid\Uuid;

interface DeploymentEvent
{
    /**
     * @return Uuid
     */
    public function getDeploymentUuid();
}
