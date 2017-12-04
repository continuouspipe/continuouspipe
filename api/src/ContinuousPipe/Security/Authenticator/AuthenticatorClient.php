<?php

namespace ContinuousPipe\Security\Authenticator;

use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\AccountNotFound;
use ContinuousPipe\Security\ApiKey\UserApiKey;
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

interface AuthenticatorClient
{
    /**
     * Get a user from its username.
     *
     * @param string $username
     *
     * @return User
     *
     * @throws UserNotFound
     */
    public function getUserByUsername($username);

    /**
     * Find the bucket object by its Uuid.
     *
     * @param UuidInterface $uuid
     *
     * @return Bucket
     *
     * @throws BucketNotFound
     */
    public function findBucketByUuid(UuidInterface $uuid);

    /**
     * @param string $slug
     *
     * @return Team
     *
     * @throws TeamNotFound
     */
    public function findTeamBySlug($slug);

    /**
     * @param $slug
     *
     * @return TeamUsageLimits
     *
     * @throws TeamNotFound
     */
    public function findTeamUsageLimitsBySlug(string $slug) : TeamUsageLimits;

    /**
     * Find all teams.
     *
     * @return Team[]
     */
    public function findAllTeams();

    /**
     * @param string $slug
     *
     * @throws TeamNotFound
     * @throws OperationFailedException
     */
    public function deleteTeamBySlug(string $slug);

    /**
     * @param UuidInterface $uuid
     *
     * @throws AccountNotFound
     *
     * @return Account
     */
    public function findAccountByUuid(UuidInterface $uuid);

    /**
     * @param string $username
     *
     * @return Account[]
     */
    public function findAccountsByUser(string $username);

    /**
     * @param string $key
     *
     * @return User|null
     */
    public function findUserByApiKey($key);

    /**
     * @param string $description
     * @param User   $user
     *
     * @return UserApiKey
     */
    public function createApiKey(User $user, string $description);

    /**
     * @param UuidInterface $bucketUuid
     * @param DockerRegistry $credentials
     *
     * @throws AuthenticatorException
     */
    public function addDockerRegistryToBucket(UuidInterface $bucketUuid, DockerRegistry $credentials);

    /**
     * @param UuidInterface $bucketUuid
     * @param Cluster $cluster
     *
     * @throws AuthenticatorException
     */
    public function addClusterToBucket(UuidInterface $bucketUuid, Cluster $cluster);

    /**
     * @param UuidInterface $bucketUuid
     * @param string $address
     * @param array $attributes
     *
     * @throws AuthenticatorException
     */
    public function updateRegistryAttributes(UuidInterface $bucketUuid, string $address, array $attributes);
}
