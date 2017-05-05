<?php

namespace ContinuousPipe\River\EventStore;

use ContinuousPipe\Events\EventStore\EventStore as ContinuousPipeEventsEventStore;

class ContinuousPipeEventsAdapter implements EventStore
{
    /**
     * @var ContinuousPipeEventsEventStore
     */
    private $eventStore;

    /**
     * @param ContinuousPipeEventsEventStore $eventStore
     */
    public function __construct(ContinuousPipeEventsEventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function store(string $stream, $event)
    {
        return $this->eventStore->store($stream, $event);
    }

    public function read(string $stream): array
    {
        return $this->eventStore->read($stream);
    }
}
