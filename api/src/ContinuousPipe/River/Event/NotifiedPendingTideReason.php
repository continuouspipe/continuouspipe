<?php

namespace ContinuousPipe\River\Event;

use Ramsey\Uuid\UuidInterface;

class NotifiedPendingTideReason implements TideEvent
{
    /**
     * @var UuidInterface
     */
    private $tideUuid;
    /**
     * @var string
     */
    private $logIdentifier;

    public function __construct(UuidInterface $tideUuid, string $logIdentifier)
    {
        $this->tideUuid = $tideUuid;
        $this->logIdentifier = $logIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return string
     */
    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }
}
