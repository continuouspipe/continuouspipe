<?php

namespace ContinuousPipe\Builder\Aggregate\FromEvents;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Aggregate\BuildRepository;
use ContinuousPipe\Events\Aggregate;
use ContinuousPipe\Events\AggregateNotFound;
use ContinuousPipe\Events\EventStore\EventStore;

class FromEventsBuildRepository implements BuildRepository
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

    /**
     * @return Build
     */
    public function find(string $aggregateIdentifier): Aggregate
    {
        $events = $this->eventStore->read((string) EventStream::fromBuildIdentifier($aggregateIdentifier));

        if (0 === count($events)) {
            throw new AggregateNotFound(sprintf(
                'Build "%s" is not found',
                $aggregateIdentifier
            ));
        }

        return Build::fromEvents($events);
    }
}
