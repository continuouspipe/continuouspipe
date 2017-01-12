<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep;

use ContinuousPipe\Events\AggregateNotFound;

interface BuildStepRepository
{
    /**
     * @param string $buildIdentifier
     * @param int $stepPosition
     *
     * @throws AggregateNotFound
     *
     * @return BuildStep
     */
    public function find(string $buildIdentifier, int $stepPosition): BuildStep;
}
