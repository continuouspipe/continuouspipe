<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use ContinuousPipe\Security\Tests\Authenticator\InMemoryAuthenticatorClient;
use Rhumsaa\Uuid\Uuid;

class SecurityContext implements Context
{
    /**
     * @var InMemoryAuthenticatorClient
     */
    private $inMemoryAuthenticatorClient;

    /**
     * @param InMemoryAuthenticatorClient $inMemoryAuthenticatorClient
     */
    public function __construct(InMemoryAuthenticatorClient $inMemoryAuthenticatorClient)
    {
        $this->inMemoryAuthenticatorClient = $inMemoryAuthenticatorClient;
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
