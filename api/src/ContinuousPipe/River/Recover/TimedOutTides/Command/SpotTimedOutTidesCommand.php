<?php

namespace ContinuousPipe\River\Recover\TimedOutTides\Command;

use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class SpotTimedOutTidesCommand
{
    /**
     * @JMS\Type("Ramsey\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $flowUuid;

    /**
     * @param Uuid $flowUuid
     */
    public function __construct(Uuid $flowUuid)
    {
        $this->flowUuid = $flowUuid;
    }

    /**
     * @return Uuid
     */
    public function getFlowUuid()
    {
        return $this->flowUuid;
    }
}
