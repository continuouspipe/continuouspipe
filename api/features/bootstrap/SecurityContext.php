<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamNotFound;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\Tests\Authenticator\InMemoryAuthenticatorClient;
use ContinuousPipe\Security\Tests\Team\InMemoryTeamRepository;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Ramsey\Uuid\Uuid;
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
        $this->iAmAuthenticatedAs('samuel');
    }

    /**
     * @Given I am authenticated as :username
     */
    public function iAmAuthenticatedAs($username)
    {
        $user = new User($username, Uuid::uuid1());

        $this->inMemoryAuthenticatorClient->addUser($user);

        $token = new JWTUserToken(['ROLE_USER']);
        $token->setUser(new SecurityUser($user));
        $this->tokenStorage->setToken($token);
    }

    /**
     * @Given the team :slug exists
     */
    public function theTeamExists($slug)
    {
        try {
            $team = $this->inMemoryAuthenticatorClient->findTeamBySlug($slug);
        } catch (TeamNotFound $e) {
            $bucket = new Bucket(Uuid::uuid1());
            $this->inMemoryAuthenticatorClient->addBucket($bucket);

            $team = new Team($slug, $slug, $bucket->getUuid());
            $this->inMemoryAuthenticatorClient->addTeam($team);
        }

        return $team;
    }

    /**
     * @Given the user :username is :permission of the team :team
     */
    public function theUserIsOfTheTeam($username, $permission, $team)
    {
        $team = $this->inMemoryAuthenticatorClient->findTeamBySlug($team);
        $user = $this->inMemoryAuthenticatorClient->getUserByUsername($username);

        $memberships = $team->getMemberships()->filter(function(TeamMembership $teamMembership) use ($user) {
            return $teamMembership->getUser()->getUsername() == $user->getUsername();
        });

        $memberships->add(new TeamMembership($team, $user, [$permission]));

        $team = new Team(
            $team->getSlug(),
            $team->getName(),
            $team->getBucketUuid(),
            $memberships->toArray()
        );

        $this->inMemoryAuthenticatorClient->addTeam($team);
    }
}
