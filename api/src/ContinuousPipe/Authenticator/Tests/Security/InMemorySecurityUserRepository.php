<?php

namespace ContinuousPipe\Authenticator\Tests\Security;

use ContinuousPipe\Authenticator\Security\User\SecurityUserRepository;
use ContinuousPipe\Authenticator\Security\User\UserNotFound;
use ContinuousPipe\User\SecurityUser;

class InMemorySecurityUserRepository implements SecurityUserRepository
{
    /**
     * @var array
     */
    private $users = [];

    /**
     * {@inheritdoc}
     */
    public function findOneByEmail($email)
    {
        if (!array_key_exists($email, $this->users)) {
            throw new UserNotFound(sprintf(
                'user with email "%s" not found',
                $email
            ));
        }

        return $this->users[$email];
    }

    /**
     * {@inheritdoc}
     */
    public function save(SecurityUser $user)
    {
        $this->users[$user->getUser()->getEmail()] = $user;
    }
}
