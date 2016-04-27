<?php

namespace ContinuousPipe\River\Recover\CancelTides\Command;

use Rhumsaa\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class CancelTideCommand
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
     * @return Uuid
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }
}
