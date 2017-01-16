<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep\FromEvents;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Aggregate\BuildRepository;
use ContinuousPipe\Builder\Aggregate\BuildStep\BuildStep;
use ContinuousPipe\Builder\Aggregate\BuildStep\BuildStepRepository;
use ContinuousPipe\Events\Aggregate;
use ContinuousPipe\Events\AggregateNotFound;
use ContinuousPipe\Events\EventStore\EventStore;

class FromEventsBuildStepRepository implements BuildStepRepository
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param EventStore $eventStore
     */
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function find(string $buildIdentifier, int $stepPosition): BuildStep
    {
        $events = $this->eventStore->read((string) EventStream::fromBuildAndStep($buildIdentifier, $stepPosition));

        if (0 === count($events)) {
            throw new AggregateNotFound(sprintf(
                'Step #%d of build "%s" is not found',
                $stepPosition,
                $buildIdentifier
            ));
        }

        return BuildStep::fromEvents($events);
    }
}
