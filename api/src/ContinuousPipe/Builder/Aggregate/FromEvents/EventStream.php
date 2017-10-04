<?php

namespace ContinuousPipe\Builder\Aggregate\FromEvents;

use ContinuousPipe\Events\EventStream\AbstractEventStream;

class EventStream extends AbstractEventStream
{
    public static function fromBuildIdentifier(string $identifier)
    {
        return new self('build-'.$identifier);
    }
}
