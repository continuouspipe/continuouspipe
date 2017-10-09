<?php

namespace ContinuousPipe\River\Event;

use Ramsey\Uuid\UuidInterface;

interface TideEvent
{
    /**
     * Get tide UUID.
     *
     * @return UuidInterface
     */
    public function getTideUuid();
}
