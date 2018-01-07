<?php

namespace ContinuousPipe\River\Task\ManualApproval\Command;

use ContinuousPipe\River\Command\TideCommand;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;

abstract class ManualApprovalCommand implements TideCommand
{
    private $tideUuid;
    private $taskIdentifier;
    private $user;

    public function __construct(UuidInterface $tideUuid, string $taskIdentifier, User $user)
    {
        $this->tideUuid = $tideUuid;
        $this->taskIdentifier = $taskIdentifier;
        $this->user = $user;
    }

    public function getTideUuid(): UuidInterface
    {
        return $this->tideUuid;
    }

    public function getTaskIdentifier(): string
    {
        return $this->taskIdentifier;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
