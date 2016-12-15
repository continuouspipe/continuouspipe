<?php

namespace ContinuousPipe\River\Task\ManualApproval\Event;

use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;

class ManualApprovalChoiceEvent extends ManualApprovalEvent
{
    private $user;

    public function __construct(UuidInterface $tideUuid, string $taskIdentifier, User $user)
    {
        parent::__construct($tideUuid, $taskIdentifier);

        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
