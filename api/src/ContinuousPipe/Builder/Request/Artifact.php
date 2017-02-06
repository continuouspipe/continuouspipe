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

    public function __construct(string $identifier, string $path)
    {
        $this->identifier = $identifier;
        $this->path = $path;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
