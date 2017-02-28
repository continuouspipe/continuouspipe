<?php

namespace ContinuousPipe\DevelopmentEnvironment\Aggregate\FromEvents;

use ContinuousPipe\Events\EventStream\AbstractEventStream;

class EventStream extends AbstractEventStream
{
    public static function fromDevelopmentEnvironmentIdentifier(string $identifier)
    {
        return new self('development-environment-'.$identifier);
    }
}
