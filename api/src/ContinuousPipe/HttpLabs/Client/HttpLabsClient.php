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
     * @param array $middlewares
     *
     * @throws HttpLabsException
     *
     * @return Stack
     */
    public function createStack(string $apiKey, string $projectIdentifier, string $name, string $backendUrl, array $middlewares) : Stack;

    /**
     * Update the given stack.
     *
     * @param string $apiKey
     * @param string $stackIdentifier
     * @param string $backendUrl
     * @param array $middlewares
     *
     * @throws HttpLabsException
     */
    public function updateStack(string $apiKey, string $stackIdentifier, string $backendUrl, array $middlewares);

    /**
     * Delete the given stack.
     *
     * @param string $apiKey
     * @param string $stackIdentifier
     *
     * @throws HttpLabsException
     */
    public function deleteStack(string $apiKey, string $stackIdentifier);
}
