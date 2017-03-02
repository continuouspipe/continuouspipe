<?php

namespace ContinuousPipe\HttpLabs\Client;

interface HttpLabsClient
{
    /**
     * Create the given HttpLabs stack.
     *
     * @param string $apiKey
     * @param string $projectIdentifier
     * @param string $backendUrl
     *
     * @throws HttpLabsException
     *
     * @return Stack
     */
    public function createStack(string $apiKey, string $projectIdentifier, string $backendUrl) : Stack;
}
