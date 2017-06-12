<?php

namespace ContinuousPipe\River\Command;

use ContinuousPipe\Message\Message;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class PinBranch implements FlowCommand, Message
{
    /**
     * @JMS\Type("Ramsey\Uuid\Uuid")
     *
     * @var Uuid
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

    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }

    public function getBranch()
    {
        return $this->branch;
    }

}
