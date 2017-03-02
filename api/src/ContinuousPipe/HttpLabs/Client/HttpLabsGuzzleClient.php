<?php

namespace ContinuousPipe\HttpLabs\Client;

class HttpLabsGuzzleClient implements HttpLabsClient
{
    /**
     * Create the given HttpLabs stack.
     *
     * @param string $apiKey
     * @param string $projectIdentifier
     * @param string $backendUrl
     *
     * @return string
     */
    public function createStack(string $apiKey, string $projectIdentifier, string $backendUrl): Stack
    {
        // TODO: Implement createStack() method.
    }
}
