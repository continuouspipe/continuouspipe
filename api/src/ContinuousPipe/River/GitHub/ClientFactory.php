<?php

namespace ContinuousPipe\River\GitHub;

use ContinuousPipe\Security\Credentials\Bucket;
use Github\Client;
use ContinuousPipe\Security\User\User;

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
     */
    public function createClientFromBucket(Bucket $credentialsBucket);

    /**
     * @return Client
     */
    public function createClientForCurrentUser();
}
