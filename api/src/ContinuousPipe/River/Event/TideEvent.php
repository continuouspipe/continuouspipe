<?php

namespace ContinuousPipe\River\Event;

use Rhumsaa\Uuid\Uuid;

interface TideEvent
{
    /**
     * Get tide UUID.
     *
     * @return Uuid
     */
    public function getTideUuid();
}
