<?php

namespace ContinuousPipe\River\EventStore;

class FallbackToNonEmptyEventStore implements EventStore
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var EventStore
     */
    private $fallback;

    /**
     * @param EventStore $eventStore
     * @param EventStore $fallback
     */
    public function __construct(EventStore $eventStore, EventStore $fallback)
    {
        $this->eventStore = $eventStore;
        $this->fallback = $fallback;
    }

    public function store(string $stream, $event)
    {
        $this->eventStore->store($stream, $event);
        $this->fallback->store($stream, $event);
    }

    public function read(string $stream): array
    {
        if (empty($events = $this->eventStore->read($stream))) {
            $events = $this->fallback->read($stream);
        }

        return $events;
    }
}
