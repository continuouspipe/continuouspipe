<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep\FromEvents;

use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepEvent;
use ContinuousPipe\Builder\Aggregate\Event\BuildEvent;
use ContinuousPipe\Events\EventStore\EventStreamResolver;

class BuildStepEventStreamResolver implements EventStreamResolver
{
    /**
     * {@inheritdoc}
     */
    public function streamByEvent($event)
    {
        if ($event instanceof StepEvent) {
            return (string) EventStream::fromEvent($event);
        }
    }
}
