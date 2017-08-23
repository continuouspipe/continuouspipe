<?php

namespace ContinuousPipe\River\Task\ManualApproval\Event;

use ContinuousPipe\River\Task\AbstractTaskEvent;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;

class ManualApprovalChoiceEvent extends AbstractTaskEvent
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
