<?php

namespace ContinuousPipe\Pipe\EventBus;

use ContinuousPipe\Pipe\Event\DeploymentEvent;
use Rhumsaa\Uuid\Uuid;

interface EventStore
{
    /**
     * @param DeploymentEvent $event
     */
    public function add(DeploymentEvent $event);

    /**
     * @param Uuid $uuid
     *
     * @return DeploymentEvent[]
     */
    public function findByDeploymentUuid(Uuid $uuid);
}
