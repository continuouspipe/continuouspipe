<?php

namespace ContinuousPipe\Authenticator;

use ContinuousPipe\User\DockerRegistryCredentials;
use ContinuousPipe\User\User;

interface DockerRegistryCredentialsRepository
{
    /**
     * @param User $user
     * @param string $serverAddress
     * @return DockerRegistryCredentials
     * @throws CredentialsNotFound
     */
    public function findByUserAndServer(User $user, $serverAddress);
}
