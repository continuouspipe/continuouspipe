<?php

namespace ContinuousPipe\Adapter\HttpLabs\Tests;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;

class EndpointProxier implements \ContinuousPipe\Adapter\HttpLabs\Endpoint\EndpointProxier
{
    public function createProxy(PublicEndpoint $endpoint, $name, Component $component)
    {
        if ($endpoint->getAddress() == '1.2.3.4') {
            return 'badger-carrot-5678.httplabs.io';
        }

        return 'monkey-potato-5678.httplabs.io';
    }
}
