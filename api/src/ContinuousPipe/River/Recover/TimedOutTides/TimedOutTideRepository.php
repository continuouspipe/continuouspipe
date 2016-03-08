<?php

namespace ContinuousPipe\River\Recover\TimedOutTides;

use ContinuousPipe\River\View\Tide;
use Rhumsaa\Uuid\Uuid;

interface TimedOutTideRepository
{
    /**
     * @param Uuid $uuid
     *
     * @return Tide[]
     */
    public function findByFlow(Uuid $uuid);
}
