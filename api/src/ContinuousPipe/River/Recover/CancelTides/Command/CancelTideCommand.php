<?php

namespace ContinuousPipe\River\Recover\CancelTides\Command;

use ContinuousPipe\River\Command\TideCommand;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;

class CancelTideCommand implements TideCommand
{
    /**
     * @JMS\Type("Ramsey\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @JMS\Type("ContinuousPipe\Security\User\User")
     *
     * @var User
     */
    private $user;

    /**
     * @param Uuid $tideUuid
     * @param User $user
     */
    public function __construct(Uuid $tideUuid, User $user)
    {
        $this->tideUuid = $tideUuid;
        $this->user = $user;
    }

    public function getTideUuid(): UuidInterface
    {
        return $this->tideUuid;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
