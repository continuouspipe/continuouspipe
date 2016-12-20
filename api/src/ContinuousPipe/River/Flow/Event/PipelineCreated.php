<?php

namespace ContinuousPipe\River\Flow\Event;

use ContinuousPipe\River\Flow\Projections\FlatPipeline;
use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class PipelineCreated implements FlowEvent
{
    /**
     * @JMS\Type("uuid")
     */
    private $flowUuid;

    /**
     * @JMS\Type("ContinuousPipe\River\Flow\Projections\FlatPipeline")
     */
    private $flatPipeline;

    public function __construct(UuidInterface $flowUuid, FlatPipeline $flatPipeline)
    {
        $this->flowUuid = $flowUuid;
        $this->flatPipeline = $flatPipeline;
    }

    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }

    public function getFlatPipeline(): FlatPipeline
    {
        return $this->flatPipeline;
    }
}
