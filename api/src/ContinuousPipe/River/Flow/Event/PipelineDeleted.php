<?php

namespace ContinuousPipe\River\Flow\Event;

use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class PipelineDeleted implements FlowEvent
{

    /**
     * @var UuidInterface
     *
     * @JMS\Type("uuid")
     */
    private $flowUuid;

    /**
     * @var UuidInterface
     *
     * @JMS\Type("uuid")
     */
    private $pipelineUuid;

    public function __construct(UuidInterface $flowUuid, UuidInterface $pipelineUuid)
    {
        $this->flowUuid = $flowUuid;
        $this->pipelineUuid = $pipelineUuid;
    }

    /**
     * @return UuidInterface
     */
    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }

    /**
     * @return UuidInterface
     */
    public function getPipelineUuid(): UuidInterface
    {
        return $this->pipelineUuid;
    }
}
