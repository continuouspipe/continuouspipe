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
     * @param string $identifier
     * @param string $path
     * @param bool $persistent
     */
    public function __construct($identifier, $path, bool $persistent = false)
    {
        $this->identifier = $identifier;
        $this->path = $path;
        $this->persistent = $persistent;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return bool
     */
    public function isPersistent(): bool
    {
        return $this->persistent ?: false;
    }
}
