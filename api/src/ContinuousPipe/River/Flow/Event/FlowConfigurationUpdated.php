<?php

namespace ContinuousPipe\River\Flow\Event;

use Ramsey\Uuid\UuidInterface;

class FlowConfigurationUpdated implements FlowEvent
{
    /**
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @param UuidInterface $flowUuid
     * @param array         $configuration
     */
    public function __construct(UuidInterface $flowUuid, array $configuration)
    {
        $this->flowUuid = $flowUuid;
        $this->configuration = $configuration;
    }

    public function getFlowUuid() : UuidInterface
    {
        return $this->flowUuid;
    }

    public function getConfiguration() : array
    {
        return $this->configuration;
    }
}
