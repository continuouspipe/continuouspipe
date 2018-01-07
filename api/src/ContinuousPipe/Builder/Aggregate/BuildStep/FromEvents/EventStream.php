<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep\FromEvents;

use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepEvent;
use ContinuousPipe\Events\EventStream\AbstractEventStream;

class EventStream extends AbstractEventStream
{
    public static function fromEvent(StepEvent $event)
    {
        return self::fromBuildAndStep(
            $event->getBuildIdentifier(),
            $event->getStepPosition()
        );
    }

    public static function fromBuildAndStep($buildIdentifier, $stepPosition)
    {
        return new self(sprintf(
            'build-%s-step-%d',
            $buildIdentifier,
            $stepPosition
        ));
    }
}
