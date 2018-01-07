<?php

namespace ContinuousPipe\Builder\Aggregate;

use ContinuousPipe\Events\Aggregate;
use ContinuousPipe\Events\AggregateRepository;

interface BuildRepository extends AggregateRepository
{
    /**
     * @param string $aggregateIdentifier
     *
     * @return Build
     */
    public function find(string $aggregateIdentifier) : Aggregate;
}
