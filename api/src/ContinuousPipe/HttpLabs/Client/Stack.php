<?php

namespace ContinuousPipe\HttpLabs\Client;

final class Stack
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $url;

    public function __construct(string $identifier, string $url)
    {
        $this->identifier = $identifier;
        $this->url = $url;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
