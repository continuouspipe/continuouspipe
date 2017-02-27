<?php

namespace ContinuousPipe\DevelopmentEnvironment\Aggregate\FromEvents;

use ContinuousPipe\DevelopmentEnvironment\Aggregate\Events\DevelopmentEnvironmentEvent;
use ContinuousPipe\Events\EventStore\EventStreamResolver;

class DevelopmentEnvironmentEventStreamResolver implements EventStreamResolver
{
    /**
     * {@inheritdoc}
     */
    public function streamByEvent($event)
    {
        if ($event instanceof DevelopmentEnvironmentEvent) {
            return (string) EventStream::fromDevelopmentEnvironmentIdentifier($event->getDevelopmentEnvironmentUuid());
        }
    }
}
