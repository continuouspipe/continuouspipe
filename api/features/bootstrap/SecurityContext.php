<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Security\Account\BitBucketAccount;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use ContinuousPipe\Security\Credentials\DockerRegistry;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
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
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @var User|null
     */
    private $currentUser;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        InMemoryAuthenticatorClient $inMemoryAuthenticatorClient,
        KernelInterface $kernel
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->inMemoryAuthenticatorClient = $inMemoryAuthenticatorClient;
        $this->kernel = $kernel;
    }

    /**
     * @Given I am authenticated
     */
    public function iAmAuthenticated()
    {
        $this->iAmAuthenticatedAs('samuel.roze@gmail.com');
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

        $this->currentUser = $user;
    }

    /**
     * @Given the team :slug exists
     * @Given there is a team :slug
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

    /**
     * @Given the team :team have the credentials of a cluster :cluster
     * @Given the team :team have the credentials of a cluster :cluster with address :address
     */
    public function theTeamHaveTheCredentialsOfACluster($team, $cluster, $address = null)
    {
        $team = $this->inMemoryAuthenticatorClient->findTeamBySlug($team);
        $bucket = $this->inMemoryAuthenticatorClient->findBucketByUuid($team->getBucketUuid());
        $address = $address ?: 'https://1.2.3.4';

        $bucket->getClusters()->add(new Kubernetes($cluster, $address, 'v1', '', ''));

        $this->inMemoryAuthenticatorClient->addBucket($bucket);
    }

    /**
     * @Given the team :team have the credentials of a Docker registry :registry
     */
    public function theTeamHaveTheCredentialsOfADockerRegistry($team, $registry)
    {
        $team = $this->inMemoryAuthenticatorClient->findTeamBySlug($team);
        $bucket = $this->inMemoryAuthenticatorClient->findBucketByUuid($team->getBucketUuid());

        $bucket->getDockerRegistries()->add(new DockerRegistry('username', 'password', 'email@example.com', $registry));

        $this->inMemoryAuthenticatorClient->addBucket($bucket);
    }

    /**
     * @Given the user :username is a ghost
     */
    public function theUserIsAGhost($username)
    {
        $user = $this->inMemoryAuthenticatorClient->getUserByUsername($username);
        $user->setRoles(array_merge($user->getRoles(), ['ROLE_GHOST']));

        $this->inMemoryAuthenticatorClient->addUser($user);
    }

    /**
     * @Given I have a BitBucket account :uuid for the user :username
     */
    public function iHaveABitbucketAccountForTheUser($uuid, $username)
    {
        $this->inMemoryAuthenticatorClient->addAccount(
            $this->currentUser,
            new BitBucketAccount(
                Uuid::fromString($uuid),
                $username,
                $username,
                $username.'@example.com',
                'refresh-token'
            )
        );
    }

    /**
     * @Given I send a :method request to the path :path
     */
    public function iSendARequestToThePath($method, $path)
    {
        $this->response = $this->kernel->handle(Request::create($path, $method));
    }

    /**
     * @Then the status code of the response should be :code
     */
    public function theStatusCodeOfTheResponseShouldBe($code)
    {
        if ($this->response->getStatusCode() != $code) {
            throw new \RuntimeException(sprintf(
                'Expected code %d but got %d',
                $code,
                $this->response->getStatusCode()
            ));
        }
    }
}
