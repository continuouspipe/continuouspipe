<?php

namespace ContinuousPipe\Builder;

class LogStreamLogging
{
    /**
     * @var string
     */
    private $parentLogIdentifier;

    /**
     * @param string $parentLogIdentifier
     *
     * @return LogStreamLogging
     */
    public static function fromParentLogIdentifier($parentLogIdentifier)
    {
        $self = new self();
        $self->parentLogIdentifier = $parentLogIdentifier;

        return $self;
    }

    /**
     * @return string
     */
    public function getParentLogIdentifier()
    {
        return $this->parentLogIdentifier;
    }
}
