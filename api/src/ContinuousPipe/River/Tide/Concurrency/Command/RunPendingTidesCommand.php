<?php

namespace ContinuousPipe\River\Tide\Concurrency\Command;

use ContinuousPipe\River\Command\FlowCommand;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;

class RunPendingTidesCommand implements FlowCommand
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

    /**
     * @param Uuid   $flowUuid
     * @param string $branch
     */
    public function __construct(Uuid $flowUuid, $branch)
    {
        $this->flowUuid = $flowUuid;
        $this->branch = $branch;
    }

    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        return $this->branch;
    }
}
