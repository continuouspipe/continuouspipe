<?php

namespace ContinuousPipe\Builder;

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

    /**
     * @var string
     */
    private $name;

    public function __construct(string $identifier, string $path, bool $persistent = false, string $name = null)
    {
        $this->identifier = $identifier;
        $this->path = $path;
        $this->persistent = $persistent;
        $this->name = $name;
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
        return $this->persistent ?: false;
    }

    public function getName() : string
    {
        return $this->name ?: $this->path;
    }
}
