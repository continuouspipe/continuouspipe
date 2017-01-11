<?php

namespace ContinuousPipe\Builder\Aggregate\Event;

abstract class BuildEvent
{
    /**
     * @var string
     */
    private $buildIdentifier;

    public function __construct(string $buildIdentifier)
    {
        $this->buildIdentifier = $buildIdentifier;
    }

    /**
     * @return string
     */
    public function getBuildIdentifier(): string
    {
        return $this->buildIdentifier;
    }
}
