<?php

namespace ContinuousPipe\Authenticator\Invitation\Event;

use ContinuousPipe\Authenticator\Invitation\UserInvitation;
use Symfony\Component\EventDispatcher\Event;

class UserInvited extends Event
{
    const EVENT_NAME = 'invitation.user_invited';

    /**
     * @var UserInvitation
     */
    private $invitation;

    /**
     * @param UserInvitation $invitation
     */
    public function __construct(UserInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * @return UserInvitation
     */
    public function getInvitation()
    {
        return $this->invitation;
    }
}
