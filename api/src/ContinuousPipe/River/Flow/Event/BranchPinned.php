<?php

namespace ContinuousPipe\River\Flow\Event;

use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class BranchPinned implements FlowEvent
{
    /**
     * @JMS\Type("uuid")
     *
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $branch;

    public function __construct(UuidInterface $flowUuid, string $branch)
    {
        $this->flowUuid = $flowUuid;
        $this->branch = $branch;
    }

    public function getFlowUuid() : UuidInterface
    {
        return $this->flowUuid;
    }

    public function getBranch()
    {
        return $this->branch;
    }
    
}
