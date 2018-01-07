<?php

namespace ContinuousPipe\River\Flow\Event;

use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class FlowRecovered implements FlowEvent
{
    /**
     * @JMS\Type("uuid")
     *
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @param UuidInterface $flowUuid
     */
    public function __construct(UuidInterface $flowUuid)
    {
        $this->flowUuid = $flowUuid;
    }

    /**
     * @return UuidInterface
     */
    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }
}
