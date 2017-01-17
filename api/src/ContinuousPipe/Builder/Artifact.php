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
