<?php

use Behat\Behat\Context\Context;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use ContinuousPipe\User\SecurityUser;
use ContinuousPipe\User\User;

class ApiContext implements Context
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Given I am authenticated
     */
    public function iAmAuthenticated()
    {
        $token = new JWTUserToken(['ROLE_USER']);
        $token->setUser(new SecurityUser(new User('samuel')));

        $this->tokenStorage->setToken($token);
    }
}
