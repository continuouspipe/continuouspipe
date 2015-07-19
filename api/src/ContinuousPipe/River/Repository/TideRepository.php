<?php

namespace ContinuousPipe\River\Repository;

use ContinuousPipe\River\Tide;
use Rhumsaa\Uuid\Uuid;

interface TideRepository
{
    /**
     * Get a tide by its UUID.
     *
     * @param Uuid $uuid
     *
     * @return Tide
     */
    public function find(Uuid $uuid);
}
