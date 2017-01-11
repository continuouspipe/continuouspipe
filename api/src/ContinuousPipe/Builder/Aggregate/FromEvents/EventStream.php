<?php

namespace ContinuousPipe\Builder\Aggregate\FromEvents;

class EventStream
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    private function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function fromBuildIdentifier(string $identifier)
    {
        return new self('build-'.$identifier);
    }

    public function __toString()
    {
        return $this->name;
    }
}
