<?php

namespace ContinuousPipe\Events\EventStream;

abstract class AbstractEventStream
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    protected function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }
}
