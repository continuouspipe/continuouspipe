<?php

namespace ContinuousPipe\Authenticator\Invitation\Event;

use ContinuousPipe\Authenticator\EarlyAccess\EarlyAccessCode;
use ContinuousPipe\Security\User\User;
use Symfony\Component\EventDispatcher\Event;

class EarlyAccessCodeEntered extends Event
{
    const EVENT_NAME = 'invitation.early_access_code_entered';

    /**
     * @var User
     */
    private $user;

    /**
     * @var EarlyAccessCode
     */
    private $earlyAccessCode;

    public function __construct(User $user, EarlyAccessCode $earlyAccessCode)
    {
        $this->user = $user;
        $this->earlyAccessCode = $earlyAccessCode;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return EarlyAccessCode
     */
    public function getEarlyAccessCode(): EarlyAccessCode
    {
        return $this->earlyAccessCode;
    }
}
