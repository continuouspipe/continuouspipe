<?php

namespace ContinuousPipe\HttpLabs\Client;

interface HttpLabsClient
{
    /**
     * Create the given HttpLabs stack.
     *
     * @param string $apiKey
     * @param string $projectIdentifier
     * @param string $name
     * @param string $backendUrl
     *
     * @throws HttpLabsException
     *
     * @return Stack
     */
    public function createStack(string $apiKey, string $projectIdentifier, string $name, string $backendUrl) : Stack;

    /**
     * Update the given stack.
     *
     * @param string $apiKey
     * @param string $stackIdentifier
     * @param string $backendUrl
     *
     * @throws HttpLabsException
     */
    public function updateStack(string $apiKey, string $stackIdentifier, string $backendUrl) : void;
}
