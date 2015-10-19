<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Security\Tests\Authenticator\InMemoryAuthenticatorClient;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;

class ApiContext implements Context
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var InMemoryAuthenticatorClient
     */
    private $authenticatorClient;

    /**
     * @param TokenStorageInterface       $tokenStorage
     * @param InMemoryAuthenticatorClient $authenticatorClient
     */
    public function __construct(TokenStorageInterface $tokenStorage, InMemoryAuthenticatorClient $authenticatorClient)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticatorClient = $authenticatorClient;
    }

    /**
     * @Given I am authenticated
     */
    public function iAmAuthenticated()
    {
        $user = new User('samuel', Uuid::uuid1());
        $this->authenticatorClient->addUser($user);

        $token = new JWTUserToken(['ROLE_USER']);
        $token->setUser(new SecurityUser($user));

        $this->tokenStorage->setToken($token);
    }
}
