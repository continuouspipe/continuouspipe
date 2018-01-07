<?php

namespace ContinuousPipe\River\LogStream\ArchiveLogs\Command;

use ContinuousPipe\Message\Delay\DelayedMessage;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class ArchiveTideCommand implements DelayedMessage
{
    /**
     * @JMS\Type("Ramsey\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $logId;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $runAt;

    public function __construct(Uuid $tideUuid, string $logId, \DateTimeInterface $runAt = null)
    {
        $this->tideUuid = $tideUuid;
        $this->logId = $logId;
        $this->runAt = $runAt;
    }

    /**
     * @return Uuid
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return string
     */
    public function getLogId()
    {
        return $this->logId;
    }

    /**
     * {@inheritdoc}
     */
    public function runAt(): \DateTimeInterface
    {
        return $this->runAt ?: new \DateTime('yesterday');
    }
}
