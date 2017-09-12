<?php

namespace ContinuousPipe\River\Filter;

use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use Ramsey\Uuid\UuidInterface;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Tide;

interface ContextFactory
{
    /**
     * Create the context available in tasks' filters.
     *
     * @param UuidInterface $flowUuid
     * @param CodeReference $codeReference
     * @param Tide|null $tide
     *
     * @return ArrayObject
     */
    public function create(UuidInterface $flowUuid, CodeReference $codeReference, Tide $tide = null) : ArrayObject;
}
