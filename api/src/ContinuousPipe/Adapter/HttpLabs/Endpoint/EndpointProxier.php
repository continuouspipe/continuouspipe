<?php

namespace ContinuousPipe\Adapter\HttpLabs\Endpoint;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;

interface EndpointProxier
{
    public function createProxy(PublicEndpoint $endpoint, $name, Component $component);
}
