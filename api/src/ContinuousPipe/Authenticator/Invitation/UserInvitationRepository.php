<?php

namespace ContinuousPipe\Authenticator\Invitation;

interface UserInvitationRepository
{
    /**
     * @param string $email
     *
     * @return UserInvitation[]
     */
    public function findByUserEmail($email);

    /**
     * @param UserInvitation $userInvitation
     *
     * @return UserInvitation
     */
    public function save(UserInvitation $userInvitation);
}
