<?php

namespace ContinuousPipe\River\Event;

use Rhumsaa\Uuid\Uuid;

class TideFailed implements TideEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @param Uuid $tideUuid
     */
    public function __construct(Uuid $tideUuid)
    {
        $this->tideUuid = $tideUuid;
    }

    /**
     * @return Uuid
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }
}
