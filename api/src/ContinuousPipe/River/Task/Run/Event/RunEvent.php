<?php

namespace ContinuousPipe\River\Task\Run\Event;

use Rhumsaa\Uuid\Uuid;

interface RunEvent
{
    /**
     * Get run UUID.
     *
     * @return Uuid
     */
    public function getRunUuid();
}
