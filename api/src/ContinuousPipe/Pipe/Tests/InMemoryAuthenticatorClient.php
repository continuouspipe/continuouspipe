<?php

namespace ContinuousPipe\Pipe\Tests;

use ContinuousPipe\User\Authenticator\AuthenticatorClient;
use ContinuousPipe\User\Authenticator\CredentialsNotFound;
use ContinuousPipe\User\Authenticator\UserNotFound;
use ContinuousPipe\User\User;

class InMemoryAuthenticatorClient implements AuthenticatorClient
{
    /**
     * @var User[]
     */
    private $users = [];

    /**
     * {@inheritdoc}
     */
    public function getUserByEmail($email)
    {
        if (!array_key_exists($email, $this->users)) {
            throw new UserNotFound();
        }

        return $this->users[$email];
    }

    /**
     * {@inheritdoc}
     */
    public function getDockerCredentialsByUserEmail($email)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getDockerCredentialsByUserEmailAndServer($email, $server)
    {
        throw new CredentialsNotFound();
    }

    /**
     * @param User $user
     */
    public function addUser(User $user)
    {
        $this->users[$user->getEmail()] = $user;
    }
}
