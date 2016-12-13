<?php

namespace ContinuousPipe\River\GitHub;

use ContinuousPipe\Security\Credentials\Bucket;
use Github\Client;
use ContinuousPipe\Security\User\User;
use GitHub\Integration\Installation;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

interface ClientFactory
{
    /**
     * @param User $user
     *
     * @return Client
     *
     * @throws UserCredentialsNotFound
     */
    public function createClientForUser(User $user);

    /**
     * @param Bucket $credentialsBucket
     *
     * @return Client
     *
     * @throws UserCredentialsNotFound
     *
     * @deprecated Uses `createClientFromBucketUuid` instead
     */
    public function createClientFromBucket(Bucket $credentialsBucket);

    /**
     * @param UuidInterface $flowUuid
     *
     * @throws UserCredentialsNotFound
     *
     * @return Client
     */
    public function createClientForFlow(UuidInterface $flowUuid);

    /**
     * @param Uuid $bucketUuid
     *
     * @return Client
     *
     * @throws UserCredentialsNotFound
     */
    public function createClientFromBucketUuid(Uuid $bucketUuid);

    /**
     * @return Client
     */
    public function createClientForCurrentUser();

    /**
     * @param Installation $installation
     *
     * @return Client
     */
    public function createClientFromInstallation(Installation $installation);
}
