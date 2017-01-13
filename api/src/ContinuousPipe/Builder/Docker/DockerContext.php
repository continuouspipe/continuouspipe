<?php

namespace ContinuousPipe\Builder\Docker;

class DockerContext
{
    private $logStreamIdentifier;

    public function __construct(string $logStreamIdentifier)
    {
        $this->logStreamIdentifier = $logStreamIdentifier;
    }


    public function getLogStreamIdentifier() : string
    {
        return $this->logStreamIdentifier;
    }

    public function withLogStreamIdentifier(string $identifier) : self
    {
        $self = clone $this;
        $self->logStreamIdentifier = $identifier;

        return $self;
    }
}
