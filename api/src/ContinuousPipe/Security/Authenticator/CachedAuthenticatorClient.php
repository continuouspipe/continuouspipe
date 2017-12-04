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
use Doctrine\Common\Cache\Cache;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CachedAuthenticatorClient implements AuthenticatorClient
{
    /**
     * @var AuthenticatorClient
     */
    private $client;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var int
     */
    private $lifetime;

    /**
     * @param AuthenticatorClient $client
     * @param Cache               $cache
     * @param int                 $lifetime
     */
    public function __construct(AuthenticatorClient $client, Cache $cache, $lifetime = 1600)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->lifetime = $lifetime;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserByUsername($username)
    {
        $cacheId = sprintf('user_%s', $username);
        if (false === ($user = $this->get($cacheId))) {
            $user = $this->client->getUserByUsername($username);
            $this->set($cacheId, $user);
        }

        return $user;
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
        $cacheId = sprintf('team_%s', $slug);
        if (false === ($team = $this->get($cacheId))) {
            $team = $this->client->findTeamBySlug($slug);
            $this->set($cacheId, $team);
        }

        return $team;
    }

    /**
     * {@inheritdoc}
     */
    public function findTeamUsageLimitsBySlug(string $slug) : TeamUsageLimits
    {
        $cacheId = sprintf('team_usage_limits_%', $slug);
        if (false === ($usageLimits = $this->get($cacheId))) {
            $usageLimits = $this->client->findTeamUsageLimitsBySlug($slug);
            $this->set($cacheId, $usageLimits);
        }

        return $usageLimits;
    }

    /**
     * {@inheritdoc}
     */
    public function findAccountByUuid(UuidInterface $uuid)
    {
        $cacheId = sprintf('account_%s', $uuid->toString());
        if (false === ($account = $this->get($cacheId))) {
            $account = $this->client->findAccountByUuid($uuid);
            $this->set($cacheId, $account);
        }

        return $account;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByApiKey($key)
    {
        $cacheId = sprintf('user_by_key_%s', $key);
        if (false === ($user = $this->get($cacheId))) {
            $user = $this->client->findUserByApiKey($key);
            $this->set($cacheId, $user);
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function findAccountsByUser(string $username)
    {
        return $this->client->findAccountsByUser($username);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllTeams()
    {
        // This is not cached because it depends on the user context actually.
        return $this->client->findAllTeams();
    }

    /**
     * Get the cached value by its ID.
     *
     * @param string $key
     *
     * @return bool|mixed
     */
    private function get($key)
    {
        if (false !== ($value = $this->cache->fetch($key))) {
            return unserialize($value);
        }

        return false;
    }

    /**
     * Store the given object to cache.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->cache->save($key, serialize($value), $this->lifetime);
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

        $cacheId = sprintf('team_%s', $slug);
        $this->cache->delete($cacheId);
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
