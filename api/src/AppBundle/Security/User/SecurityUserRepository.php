<?php

namespace AppBundle\Security\User;

use Kubernetes\Manager\User\SecurityUser;

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
