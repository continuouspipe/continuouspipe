<?php

namespace ContinuousPipe\River\Flow\Event;

use ContinuousPipe\River\Flex\FlexConfiguration;
use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class FlowFlexed implements FlowEvent
{
    /**
     * @JMS\Type("uuid")
     *
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @JMS\Type("ContinuousPipe\River\Flex\FlexConfiguration")
     *
     * @var FlexConfiguration
     */
    private $flexConfiguration;

    /**
     * @param UuidInterface $flowUuid
     * @param FlexConfiguration $flexConfiguration
     */
    public function __construct(UuidInterface $flowUuid, FlexConfiguration $flexConfiguration)
    {
        $this->flowUuid = $flowUuid;
        $this->flexConfiguration = $flexConfiguration;
    }

    public function getFlowUuid() : UuidInterface
    {
        return $this->flowUuid;
    }

    public function getFlexConfiguration(): FlexConfiguration
    {
        return $this->flexConfiguration;
    }
}
