<?php

namespace ContinuousPipe\River\Task\Delete\Event;

use Ramsey\Uuid\UuidInterface;

class EnvironmentDeletionFailed extends AbstractEnvironmentDeletionEvent
{
    /**
     * @var string
     */
    private $reason;

    public function __construct(UuidInterface $tideUuid, $taskIdentifier, $logIdentifier, $label, string $reason)
    {
        parent::__construct($tideUuid, $taskIdentifier, $logIdentifier, $label);

        $this->reason = $reason;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }
}
