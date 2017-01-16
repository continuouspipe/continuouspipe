<?php

namespace ContinuousPipe\Events\EventStore;

class InMemoryEventStore implements EventStore
{
    private $streams = [];

    public function store(string $stream, $event)
    {
        if (!array_key_exists($stream, $this->streams)) {
            $this->streams[$stream] = [];
        }

        $this->streams[$stream][] = $event;
    }

    public function read(string $stream): array
    {
        if (!array_key_exists($stream, $this->streams)) {
            return [];
        }

        return $this->streams[$stream];
    }
}
