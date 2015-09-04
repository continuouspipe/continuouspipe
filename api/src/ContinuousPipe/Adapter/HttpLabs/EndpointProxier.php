<?php

namespace ContinuousPipe\Adapter\HttpLabs;

use ContinuousPipe\Pipe\Environment\PublicEndpoint;

class EndpointProxier
{
    public function createProxy(PublicEndpoint $endpoint)
    {
        if ($endpoint->getAddress() == '1.2.3.4') {
            return 'badger-carrot-5678.httplabs.io';
        }

        return 'monkey-potato-5678.httplabs.io';
    }
} 