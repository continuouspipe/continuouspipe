<?php

namespace ContinuousPipe\Security\Authenticator;

use ContinuousPipe\Security\User\User;
use ContinuousPipe\Security\User\UserRepository;

class AuthenticatorUserRepository implements UserRepository
{
    /**
     * @var AuthenticatorClient
     */
    private $authenticatorClient;

    /**
     * @param AuthenticatorClient $authenticatorClient
     */
    public function __construct(AuthenticatorClient $authenticatorClient)
    {
        $this->authenticatorClient = $authenticatorClient;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByUsername($username)
    {
        return $this->authenticatorClient->getUserByUsername($username);
    }

    public function save(User $user)
    {
        throw new \RuntimeException('Could not create a user through the authenticator\'s API');
    }
}
