<?php

namespace ContinuousPipe\Model\Component\Endpoint;

class HttpLabs
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $projectIdentifier;

    /**
     * @var array[]
     */
    private $middlewares;
    
    /**
     * @var string
     */
    private $incoming;

    public function __construct(string $apiKey, string $projectIdentifier, string $incoming = null)
    {
        $this->apiKey = $apiKey;
        $this->projectIdentifier = $projectIdentifier;
        $this->incoming = $incoming;
    }

    public function getProjectIdentifier(): string
    {
        return $this->projectIdentifier;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares ?: [];
    }

    public function getIncoming()
    {
        return $this->incoming;
    }
    
}
