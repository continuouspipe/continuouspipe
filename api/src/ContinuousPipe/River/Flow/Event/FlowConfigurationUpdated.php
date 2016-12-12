<?php

namespace ContinuousPipe\River\Flow\Event;

use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class FlowConfigurationUpdated implements FlowEvent
{
    /**
     * @JMS\Type("uuid")
     *
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @JMS\Type("string")
     * @JMS\Accessor(getter="serializeConfiguration", setter="deserializeConfiguration")
     *
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

    public function serializeConfiguration() : string
    {
        return \GuzzleHttp\json_encode($this->configuration);
    }

    public function deserializeConfiguration(string $configuration)
    {
        $this->configuration = \GuzzleHttp\json_decode($configuration, true);
    }
}
