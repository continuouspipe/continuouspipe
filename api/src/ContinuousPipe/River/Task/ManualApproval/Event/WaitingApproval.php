<?php

namespace ContinuousPipe\River\Task\ManualApproval\Event;

use Ramsey\Uuid\UuidInterface;

class WaitingApproval extends ManualApprovalEvent
{
    private $logIdentifier;
    private $label;
    private $approvalLogIdentifier;

    public function __construct(UuidInterface $tideUuid, string $taskIdentifier, string $logIdentifier, string $label, string $approvalLogIdentifier)
    {
        parent::__construct($tideUuid, $taskIdentifier);

        $this->logIdentifier = $logIdentifier;
        $this->label = $label;
        $this->approvalLogIdentifier = $approvalLogIdentifier;
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getApprovalLogIdentifier(): string
    {
        return $this->approvalLogIdentifier;
    }
}
