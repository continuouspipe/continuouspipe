<?php

namespace ContinuousPipe\River\Flow\Event;

use ContinuousPipe\River\Flex\FlexConfiguration;
use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class FlowUnflexed implements FlowEvent
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

    public function getFlowUuid() : UuidInterface
    {
        return $this->flowUuid;
    }
}
