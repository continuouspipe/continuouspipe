<?php

namespace ContinuousPipe\River\Event;

use Ramsey\Uuid\Uuid;

class TideFailed implements TideEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var string
     */
    private $reason;

    /**
     * @param Uuid   $tideUuid
     * @param string $reason
     */
    public function __construct(Uuid $tideUuid, $reason)
    {
        $this->tideUuid = $tideUuid;
        $this->reason = $reason;
    }

    /**
     * @return Uuid
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }
}
