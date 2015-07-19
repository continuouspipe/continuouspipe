<?php

namespace ContinuousPipe\River\Repository;

use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Tide;
use Rhumsaa\Uuid\Uuid;

class EventBusTideRepository implements TideRepository
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
     * {@inheritdoc}
     */
    public function find(Uuid $uuid)
    {
        $events = $this->eventStore->findByTideUuid($uuid);

        return Tide::fromEvents($events);
    }
}
