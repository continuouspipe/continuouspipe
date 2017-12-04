<?php

namespace ContinuousPipe\Security\Tests\Authenticator;

use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\AccountNotFound;
use ContinuousPipe\Security\ApiKey\UserApiKey;
use ContinuousPipe\Security\Authenticator\AuthenticatorClient;
use ContinuousPipe\Security\Authenticator\AuthenticatorException;
use ContinuousPipe\Security\Authenticator\UserNotFound;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamNotFound;
use ContinuousPipe\Security\Team\TeamUsageLimits;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InMemoryAuthenticatorClient implements AuthenticatorClient
{
    /**
     * @var User[]
     */
    private $users = [];

    /**
     * @var Team[]
     */
    private $teams = [];

    /**
     * @var TeamUsageLimits[]
     */
    private $teamUsageLimits = [];

    /**
     * @var Bucket[]
     */
    private $buckets = [];

    /**
     * @var Account[]
     */
    private $accounts = [];

    /**
     * @var array
     */
    private $accountsByUser = [];

    /**
     * @var array
     */
    private $userByApiKey = [];

    /**
     * @var callable|null
     */
    private $apiKeyCreationHook = null;

    /**
     * {@inheritdoc}
     */
    public function getUserByUsername($username)
    {
        if (!array_key_exists($username, $this->users)) {
            throw new UserNotFound(sprintf('User with username "%s" not found', $username));
        }

        return $this->users[$username];
    }

    /**
     * {@inheritdoc}
     */
    public function findTeamBySlug($slug)
    {
        if (!array_key_exists($slug, $this->teams)) {
            throw TeamNotFound::createFromSlug($slug);
        }

        return $this->teams[$slug];
    }

    /**
     * {@inheritdoc}
     */
    public function findTeamUsageLimitsBySlug(string $slug) : TeamUsageLimits
    {
        if (!array_key_exists($slug, $this->teams)) {
            throw TeamNotFound::createFromSlug($slug);
        }
        if (!array_key_exists($slug, $this->teamUsageLimits)) {
            throw TeamNotFound::createFromSlug($slug); // the real endpoint could only return team not found 404
        }

        return $this->teamUsageLimits[$slug];
    }

    /**
     * {@inheritdoc}
     */
    public function findBucketByUuid(UuidInterface $uuid)
    {
        $bucketKey = (string) $uuid;
        if (!array_key_exists($bucketKey, $this->buckets)) {
            throw new BucketNotFound(sprintf(
                'Bucket "%s" is not found',
                $uuid
            ));
        }

        return $this->buckets[$bucketKey];
    }

    /**
     * {@inheritdoc}
     */
    public function findAccountByUuid(UuidInterface $uuid)
    {
        $key = (string) $uuid;
        if (!array_key_exists($key, $this->accounts)) {
            throw new AccountNotFound(sprintf('Account "%s" not found', $key));
        }

        return $this->accounts[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function findAllTeams()
    {
        return $this->teams;
    }

    /**
     * @param User $user
     */
    public function addUser(User $user)
    {
        $this->users[$user->getUsername()] = $user;
    }

    /**
     * @param Team $team
     */
    public function addTeam(Team $team)
    {
        $this->teams[$team->getSlug()] = $team;
    }

    /**
     * @param Team $team
     * @param TeamUsageLimits $teamUsageLimits
     */
    public function addTeamUsageLimit(Team $team, TeamUsageLimits $teamUsageLimits)
    {
        $this->teamUsageLimits[$team->getSlug()] = $teamUsageLimits;
    }

    /**
     * @param Team $team
     */
    public function deleteTeam(Team $team)
    {
        unset($this->teams[$team->getSlug()]);
    }

    /**
     * @param Bucket $bucket
     */
    public function addBucket(Bucket $bucket)
    {
        $this->buckets[(string) $bucket->getUuid()] = $bucket;
    }

    /**
     * @param Account $account
     */
    public function addAccount(User $user, Account $account)
    {
        $this->accounts[(string) $account->getUuid()] = $account;

        if (!array_key_exists($user->getUsername(), $this->accountsByUser)) {
            $this->accountsByUser[$user->getUsername()] = [];
        }

        $this->accountsByUser[$user->getUsername()][] = $account;
    }

    /**
     * {@inheritdoc}
     */
    public function findAccountsByUser(string $username)
    {
        if (!array_key_exists($username, $this->accountsByUser)) {
            return [];
        }

        return $this->accountsByUser[$username];
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByApiKey($key)
    {
        if (array_key_exists($key, $this->userByApiKey)) {
            return $this->userByApiKey[$key];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function createApiKey(User $user, string $description)
    {
        if (null === $this->apiKeyCreationHook) {
            $key = new UserApiKey(
                Uuid::uuid4(),
                $user,
                Uuid::uuid4()->toString(),
                new \DateTime(),
                $description
            );
        } else {
            $creationHook = $this->apiKeyCreationHook;

            $key = $creationHook($user, $description);
        }

        $this->userByApiKey[$key->getApiKey()] = $user;

        return $key;
    }

    /**
     * @param UserApiKey $apiKey
     */
    public function addApiKey(UserApiKey $apiKey)
    {
        $this->userByApiKey[$apiKey->getApiKey()] = $apiKey->getUser();
    }

    /**
     * @param callable $apiKeyCreationHook
     */
    public function setApiKeyCreationHook(callable $apiKeyCreationHook)
    {
        $this->apiKeyCreationHook = $apiKeyCreationHook;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTeamBySlug(string $slug)
    {
        if (!isset($this->teams[$slug])) {
            throw TeamNotFound::createFromSlug($slug);
        }

        unset($this->teams[$slug]);
    }

    /**
     * {@inheritdoc}
     */
    public function addDockerRegistryToBucket(UuidInterface $bucketUuid, DockerRegistry $credentials)
    {
        $this->findBucketByUuid($bucketUuid)->getDockerRegistries()->add($credentials);
    }

    /**
     * {@inheritdoc}
     */
    public function addClusterToBucket(UuidInterface $bucketUuid, Cluster $cluster)
    {
        $this->findBucketByUuid($bucketUuid)->getClusters()->add($cluster);
    }

    /**
     * {@inheritdoc}
     */
    public function updateRegistryAttributes(UuidInterface $bucketUuid, string $address, array $attributes)
    {
        foreach ($this->findBucketByUuid($bucketUuid)->getDockerRegistries() as $registry) {
            if ($registry->getFullAddress() == $address) {
                $registry->__set('attributes', $attributes);
            }
        }
    }
}
