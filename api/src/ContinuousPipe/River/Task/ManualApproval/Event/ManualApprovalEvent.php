<?php

namespace ContinuousPipe\River\Task\ManualApproval\Event;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\TaskEvent;
use Ramsey\Uuid\UuidInterface;

abstract class ManualApprovalEvent implements TideEvent, TaskEvent
{
    private $tideUuid;
    private $taskIdentifier;
    private $dateTime;

    public function __construct(UuidInterface $tideUuid, string $taskIdentifier, \DateTimeInterface $dateTime = null)
    {
        $this->tideUuid = $tideUuid;
        $this->taskIdentifier = $taskIdentifier;
        $this->dateTime = $dateTime ?: new \DateTime();
    }

    public function getTideUuid(): UuidInterface
    {
        return $this->tideUuid;
    }

    public function getTaskId(): string
    {
        return $this->taskIdentifier;
    }

    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }
}
