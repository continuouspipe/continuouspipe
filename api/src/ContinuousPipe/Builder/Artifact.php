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
     * @param string $identifier
     * @param string $path
     */
    public function __construct($identifier, $path)
    {
        $this->identifier = $identifier;
        $this->path = $path;
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
}
