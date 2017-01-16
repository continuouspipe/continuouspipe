<?php

namespace ContinuousPipe\Events\EventStore;

interface EventStore
{
    public function store(string $stream, $event);

    public function read(string $stream) : array;
}
