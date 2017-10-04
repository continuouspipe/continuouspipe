<?php

namespace ContinuousPipe\Builder\Aggregate\FromEvents;

use ContinuousPipe\Builder\Aggregate\Event\BuildEvent;
use ContinuousPipe\Events\EventStore\EventStreamResolver;

class BuildEventStreamResolver implements EventStreamResolver
{
    /**
     * {@inheritdoc}
     */
    public function streamByEvent($event)
    {
        if ($event instanceof BuildEvent) {
            return (string) EventStream::fromBuildIdentifier($event->getBuildIdentifier());
        }
    }
}
