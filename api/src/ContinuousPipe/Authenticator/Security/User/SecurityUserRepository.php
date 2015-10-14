<?php

namespace ContinuousPipe\Authenticator\Security\User;

use ContinuousPipe\Security\User\SecurityUser;

interface SecurityUserRepository
{
    /**
     * @param string $email
     *
     * @throws UserNotFound
     *
     * @return SecurityUser
     */
    public function findOneByEmail($email);

    /**
     * @param SecurityUser $user
     */
    public function save(SecurityUser $user);
}
