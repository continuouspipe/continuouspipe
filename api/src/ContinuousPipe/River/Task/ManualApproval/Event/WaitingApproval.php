<?php

namespace ContinuousPipe\River\Task\ManualApproval\Event;

use Ramsey\Uuid\UuidInterface;

class WaitingApproval extends ManualApprovalEvent
{
    private $logIdentifier;
    private $label;

    public function __construct(UuidInterface $tideUuid, string $taskIdentifier, string $logIdentifier, string $label)
    {
        parent::__construct($tideUuid, $taskIdentifier);

        $this->logIdentifier = $logIdentifier;
        $this->label = $label;
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
