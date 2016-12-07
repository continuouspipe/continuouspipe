<?php

namespace ContinuousPipe\River\Flow\Projections;

use ContinuousPipe\River\Repository\FlowNotFound;
use Ramsey\Uuid\UuidInterface;

interface FlatFlowRepository
{
    /**
     * @param UuidInterface $uuid
     *
     * @throws FlowNotFound
     *
     * @return FlatFlow
     */
    public function find(UuidInterface $uuid);
}

