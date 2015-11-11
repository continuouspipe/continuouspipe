<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use ContinuousPipe\Security\Tests\Authenticator\InMemoryAuthenticatorClient;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityContext implements Context
{
    /**
     * @var InMemoryAuthenticatorClient
     */
    private $inMemoryAuthenticatorClient;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param InMemoryAuthenticatorClient $inMemoryAuthenticatorClient
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(InMemoryAuthenticatorClient $inMemoryAuthenticatorClient, TokenStorageInterface $tokenStorage)
    {
        $this->inMemoryAuthenticatorClient = $inMemoryAuthenticatorClient;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Given I am authenticated
     */
    public function iAmAuthenticated()
    {
        $token = new JWTUserToken(['ROLE_USER']);
        $token->setUser(new SecurityUser(new User('samuel', Uuid::uuid1())));

        $this->tokenStorage->setToken($token);
    }

    /**
     * @Given there is the bucket :uuid
     */
    public function thereIsTheBucket($uuid)
    {
        $this->inMemoryAuthenticatorClient->addBucket(new Bucket(Uuid::fromString($uuid)));
    }

    /**
     * @Given the bucket :uuid contains the following docker registry credentials:
     */
    public function theBucketContainsTheFollowingDockerRegistryCredentials($uuid, TableNode $table)
    {
        $bucket = $this->inMemoryAuthenticatorClient->findBucketByUuid(Uuid::fromString($uuid));

        foreach ($table->getHash() as $row) {
            $bucket->getDockerRegistries()->add(new DockerRegistry($row['username'], $row['password'], $row['email'], $row['serverAddress']));
        }
    }
}
