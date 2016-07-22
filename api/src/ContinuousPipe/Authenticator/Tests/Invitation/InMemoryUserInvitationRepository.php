<?php

namespace ContinuousPipe\Authenticator\Tests\Invitation;

use ContinuousPipe\Authenticator\Invitation\UserInvitation;
use ContinuousPipe\Authenticator\Invitation\UserInvitationRepository;

class InMemoryUserInvitationRepository implements UserInvitationRepository
{
    /**
     * @var UserInvitation[]
     */
    private $invitations = [];

    /**
     * {@inheritdoc}
     */
    public function findByUserEmail($email)
    {
        return array_values(array_filter($this->invitations, function (UserInvitation $userInvitation) use ($email) {
            return $userInvitation->getUserEmail() == $email;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function save(UserInvitation $userInvitation)
    {
        return $this->invitations[] = $userInvitation;
    }
}
