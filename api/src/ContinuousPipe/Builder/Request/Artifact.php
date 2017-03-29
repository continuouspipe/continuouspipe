<?php

namespace ContinuousPipe\Builder\Request;

class Artifact
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $path;

    /**
     * @var bool
     */
    private $persistent;

    public function __construct(string $identifier, string $path, bool $persistent = false)
    {
        $this->identifier = $identifier;
        $this->path = $path;
        $this->persistent = $persistent;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function isPersistent(): bool
    {
        return $this->persistent;
    }
}
