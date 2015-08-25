<?php

namespace ContinuousPipe\Pipe\Listener\PublicEndpointsCreated;

use ContinuousPipe\Pipe\Event\PublicEndpointsCreated;

class PopulateDeploymentEndpoints
{
    public function notify(PublicEndpointsCreated $event)
    {
        $context = $event->getDeploymentContext();

        $context->getDeployment()->setPublicEndpoints($event->getEndpoints());
    }
}
