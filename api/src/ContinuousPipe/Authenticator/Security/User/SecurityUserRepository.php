<?php

namespace ContinuousPipe\Authenticator\Security\User;

use ContinuousPipe\Security\User\SecurityUser;

interface SecurityUserRepository
{
    /**
     * @param string $username
     *
     * @throws UserNotFound
     *
     * @return SecurityUser
     */
    public function findOneByUsername($username);

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
     *
     * @return SecurityUser
     */
    public function save(SecurityUser $user);

    /**
     * Return the number of users.
     *
     * @return int
     */
    public function count();
}
