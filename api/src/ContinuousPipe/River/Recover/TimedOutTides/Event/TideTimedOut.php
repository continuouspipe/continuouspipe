<?php

namespace ContinuousPipe\River\Recover\TimedOutTides\Event;

use ContinuousPipe\River\Event\TideEvent;
use Rhumsaa\Uuid\Uuid;

class TideTimedOut implements TideEvent
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
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }
}
