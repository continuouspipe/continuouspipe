<?php

namespace ContinuousPipe\Events;

interface AggregateRepository
{
    /**
     * @param string $aggregateIdentifier
     *
     * @throws AggregateNotFound
     *
     * @return Aggregate
     */
    public function find(string $aggregateIdentifier) : Aggregate;
}
