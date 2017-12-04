<?php

namespace ContinuousPipe\Security\User;

interface UserRepository
{
    /**
     * @param string $username
     *
     * @return User
     */
    public function findOneByUsername($username);

    public function save(User $user);
}
