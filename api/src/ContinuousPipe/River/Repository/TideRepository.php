<?php

namespace ContinuousPipe\River\Repository;

use ContinuousPipe\River\Tide;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

interface TideRepository
{
    /**
     * Get a tide by its UUID.
     *
     * @param UuidInterface $uuid
     *
     * @throws TideNotFound
     *
     * @return Tide
     */
    public function find(UuidInterface $uuid);
}
