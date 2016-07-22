<?php

namespace ContinuousPipe\Authenticator\Tests\Security;

use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use ContinuousPipe\Authenticator\Security\User\UserNotFound;
use ContinuousPipe\Security\User\SecurityUser;

class InMemorySecurityUserRepository implements SecurityUserRepository
{
    /**
     * @var array
     */
    private $users = [];

    /**
     * {@inheritdoc}
     */
    public function findOneByUsername($username)
    {
        if (!array_key_exists($username, $this->users)) {
            throw new UserNotFound(sprintf(
                'User "%s" not found',
                $username
            ));
        }

        return $this->users[$username];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByEmail($email)
    {
        $matchingUsers = array_filter($this->users, function (SecurityUser $user) use ($email) {
            return $user->getUser()->getEmail() == $email;
        });

        if (count($matchingUsers) == 0) {
            throw new UserNotFound(sprintf(
                'User with email "%s" not found',
                $email
            ));
        }

        return current($matchingUsers);
    }

    /**
     * {@inheritdoc}
     */
    public function save(SecurityUser $user)
    {
        $this->users[$user->getUser()->getUsername()] = $user;

        return $user;
    }
}
