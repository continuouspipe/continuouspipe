<?php

namespace ContinuousPipe\Builder\Aggregate\Event;

use JMS\Serializer\Annotation as JMS;

abstract class BuildEvent
{
    /**
     * @JMS\Type("string")
     *
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
