<?php

namespace ContinuousPipe\River\Recover\CancelTides\Event;

use ContinuousPipe\River\Event\TideEvent;
use Rhumsaa\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class TideCancelled implements TideEvent
{
    /**
     * @JMS\Type("Rhumsaa\Uuid\Uuid")
     *
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
