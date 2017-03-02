<?php

namespace ContinuousPipe\HttpLabs;

use ContinuousPipe\HttpLabs\Client\HttpLabsClient;
use ContinuousPipe\HttpLabs\Client\Stack;

class TraceableClient implements HttpLabsClient
{
    /**
     * @var array
     */
    private $createdStacks = [];

    /**
     * @var HttpLabsClient
     */
    private $decoratedClient;

    public function __construct(HttpLabsClient $decoratedClient)
    {
        $this->decoratedClient = $decoratedClient;
    }

    /**
     * {@inheritdoc}
     */
    public function createStack(string $apiKey, string $projectIdentifier, string $name, string $backendUrl): Stack
    {
        $stack = $this->decoratedClient->createStack($apiKey, $projectIdentifier, $name, $backendUrl);

        $this->createdStacks[] = [
            'project_identifier' => $projectIdentifier,
            'backend_url' => $backendUrl,
            'stack' => $stack,
        ];

        return $stack;
    }

    /**
     * @return array
     */
    public function getCreatedStacks(): array
    {
        return $this->createdStacks;
    }
}
