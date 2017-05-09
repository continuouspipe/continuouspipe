<?php

namespace ContinuousPipe\River\Command;

use ContinuousPipe\Message\Delay\DelayedMessage;
use ContinuousPipe\River\CodeReference;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;

class DeleteEnvironments implements FlowCommand, DelayedMessage
{
    /**
     * @JMS\Type("Ramsey\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $flowUuid;

    /**
     * @JMS\Type("ContinuousPipe\River\CodeReference")
     *
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $runAt;

    public function __construct(Uuid $flowUuid, CodeReference $codeReference, \DateTimeInterface $runAt = null)
    {
        $this->flowUuid = $flowUuid;
        $this->codeReference = $codeReference;
        $this->runAt = $runAt;
    }

    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }

    /**
     * @return CodeReference
     */
    public function getCodeReference(): CodeReference
    {
        return $this->codeReference;
    }

    /**
     * {@inheritdoc}
     */
    public function runAt(): \DateTimeInterface
    {
        return $this->runAt ?: new \DateTime('yesterday');
    }
}
