<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\Tests\Authenticator\InMemoryAuthenticatorClient;
use ContinuousPipe\Security\Tests\Team\InMemoryTeamRepository;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityContext implements Context
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var InMemoryAuthenticatorClient
     */
    private $inMemoryAuthenticatorClient;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param InMemoryAuthenticatorClient $inMemoryAuthenticatorClient
     */
    public function __construct(TokenStorageInterface $tokenStorage, InMemoryAuthenticatorClient $inMemoryAuthenticatorClient)
    {
        $this->tokenStorage = $tokenStorage;
        $this->inMemoryAuthenticatorClient = $inMemoryAuthenticatorClient;
    }

    /**
     * @Given I am authenticated
     */
    public function iAmAuthenticated()
    {
        $token = new JWTUserToken(['ROLE_USER']);
        $token->setUser(new SecurityUser(new User('samuel.roze@gmail.com', Uuid::uuid1())));
        $this->tokenStorage->setToken($token);
    }

    /**
     * @Given the team :slug exists
     */
    public function theTeamExists($slug)
    {
        $bucket = new Bucket(Uuid::uuid1());
        $this->inMemoryAuthenticatorClient->addBucket($bucket);

        $team = new Team($slug, $bucket->getUuid());
        $this->inMemoryAuthenticatorClient->addTeam($team);

        return $team;
    }
}
