<?php

namespace ContinuousPipe\River\Tide\Concurrency\Command;

use ContinuousPipe\Message\Delay\DelayedMessage;
use ContinuousPipe\River\Command\FlowCommand;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;

class RunPendingTidesCommand implements FlowCommand, DelayedMessage
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
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $runAt;

    public function __construct(Uuid $flowUuid, $branch, \DateTimeInterface $runAt = null)
    {
        $this->flowUuid = $flowUuid;
        $this->branch = $branch;
        $this->runAt = $runAt;
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

    /**
     * {@inheritdoc}
     */
    public function runAt(): \DateTimeInterface
    {
        return $this->runAt ?: new \DateTime();
    }
}
