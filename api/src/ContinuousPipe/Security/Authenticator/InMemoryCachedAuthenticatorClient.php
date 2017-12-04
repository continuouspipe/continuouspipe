<?php

namespace ContinuousPipe\Security\Authenticator;

use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\AccountNotFound;
use ContinuousPipe\Security\ApiKey\UserApiKey;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamNotFound;
use ContinuousPipe\Security\Team\TeamUsageLimits;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InMemoryCachedAuthenticatorClient implements AuthenticatorClient
{
    /**
     * @var array
     */
    private $usersByUsername = [];

    /**
     * @var array
     */
    private $teams = [];

    /**
     * @var array
     */
    private $accounts = [];

    /**
     * @var array
     */
    private $accountsByUser = [];

    /**
     * @var array
     */
    private $teamsBySlug = [];

    /**
     * @var array
     */
    private $userByApiKey = [];

    /**
     * @var AuthenticatorClient
     */
    private $client;

    /**
     * @var array
     */
    private $teamUsageLimitsBySlug = [];

    /**
     * @param AuthenticatorClient $client
     */
    public function __construct(AuthenticatorClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserByUsername($username)
    {
        if (!array_key_exists($username, $this->usersByUsername)) {
            $this->usersByUsername[$username] = $this->client->getUserByUsername($username);
        }

        return $this->usersByUsername[$username];
    }

    /**
     * {@inheritdoc}
     */
    public function findBucketByUuid(UuidInterface $uuid)
    {
        return $this->client->findBucketByUuid($uuid);
    }

    /**
     * {@inheritdoc}
     */
    public function findTeamBySlug($slug)
    {
        if (!array_key_exists($slug, $this->teamsBySlug)) {
            $this->teamsBySlug[$slug] = $this->client->findTeamBySlug($slug);
        }

        return $this->teamsBySlug[$slug];
    }

    public function findTeamUsageLimitsBySlug(string $slug) : TeamUsageLimits
    {
        if (!array_key_exists($slug, $this->teamUsageLimitsBySlug)) {
            $this->teamUsageLimitsBySlug[$slug] = $this->client->findTeamUsageLimitsBySlug($slug);
        }

        return $this->teamUsageLimitsBySlug[$slug];
    }

    /**
     * {@inheritdoc}
     */
    public function findAllTeams()
    {
        if (empty($this->teams)) {
            $this->teams = $this->client->findAllTeams();
        }

        return $this->teams;
    }

    /**
     * {@inheritdoc}
     */
    public function findAccountByUuid(UuidInterface $uuid)
    {
        $key = (string) $uuid;
        if (!array_key_exists($key, $this->accounts)) {
            $this->accounts[$key] = $this->client->findAccountByUuid($uuid);
        }

        return $this->accounts[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function findAccountsByUser(string $username)
    {
        if (!array_key_exists($username, $this->accountsByUser)) {
            $this->accountsByUser[$username] = $this->client->findAccountsByUser($username);
        }

        return $this->accountsByUser[$username];
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByApiKey($key)
    {
        if (!array_key_exists($key, $this->userByApiKey)) {
            $this->userByApiKey[$key] = $this->client->findUserByApiKey($key);
        }

        return $this->userByApiKey[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function createApiKey(User $user, string $description)
    {
        return $this->client->createApiKey($user, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTeamBySlug(string $slug)
    {
        $this->client->deleteTeamBySlug($slug);

        unset($this->teamsBySlug[$slug]);
    }

    /**
     * {@inheritdoc}
     */
    public function addDockerRegistryToBucket(UuidInterface $bucketUuid, DockerRegistry $credentials)
    {
        return $this->client->addDockerRegistryToBucket($bucketUuid, $credentials);
    }

    /**
     * {@inheritdoc}
     */
    public function addClusterToBucket(UuidInterface $bucketUuid, Cluster $cluster)
    {
        return $this->client->addClusterToBucket($bucketUuid, $cluster);
    }

    /**
     * {@inheritdoc}
     */
    public function updateRegistryAttributes(UuidInterface $bucketUuid, string $address, array $attributes)
    {
        return $this->client->updateRegistryAttributes($bucketUuid, $address, $attributes);
    }

    /**
     * @internal `addBucket` proxy.
     */
    public function addBucket(Bucket $bucket)
    {
        $this->client->addBucket($bucket);
    }

    /**
     * @internal `addTeam` proxy.
     */
    public function addTeam(Team $team)
    {
        $this->client->addTeam($team);
    }
}
