<?php

namespace ContinuousPipe\River\Recover\TimedOutTides\Command;

use ContinuousPipe\Message\Delay\DelayedMessage;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class SpotTimedOutTidesCommand implements DelayedMessage
{
    /**
     * @JMS\Type("Ramsey\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $flowUuid;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $runAt;

    public function __construct(Uuid $flowUuid, \DateTimeInterface $runAt = null)
    {
        $this->flowUuid = $flowUuid;
        $this->runAt = $runAt;
    }

    /**
     * @return Uuid
     */
    public function getFlowUuid()
    {
        return $this->flowUuid;
    }

    /**
     * {@inheritdoc}
     */
    public function runAt(): \DateTimeInterface
    {
        return $this->runAt ?: new \DateTime('yesterday');
    }
}
