<?php

namespace ContinuousPipe\River\Event;

use Ramsey\Uuid\Uuid;

interface TideEvent
{
    /**
     * Get tide UUID.
     *
     * @return Uuid
     */
    public function getTideUuid();
}
