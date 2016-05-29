<?php

namespace ContinuousPipe\River\Repository;

use ContinuousPipe\River\Tide;
use Ramsey\Uuid\Uuid;

interface TideRepository
{
    /**
     * Get a tide by its UUID.
     *
     * @param Uuid $uuid
     *
     * @throws TideNotFound
     *
     * @return Tide
     */
    public function find(Uuid $uuid);
}
