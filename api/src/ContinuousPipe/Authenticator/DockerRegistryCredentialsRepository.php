<?php

namespace ContinuousPipe\Authenticator;

use ContinuousPipe\Security\User\User;

interface DockerRegistryCredentialsRepository
{
    /**
     * @param User   $user
     * @param string $serverAddress
     *
     * @return DockerRegistryCredentials
     *
     * @throws CredentialsNotFound
     */
    public function findOneByUserAndServer(User $user, $serverAddress);

    /**
     * @param User $user
     *
     * @return DockerRegistryCredentials[]
     */
    public function findByUser(User $user);

    /**
     * @param DockerRegistryCredentials $credentials
     * @param User                      $user
     *
     * @return DockerRegistryCredentials
     */
    public function save(DockerRegistryCredentials $credentials, User $user);

    /**
     * Remove the given credentials.
     *
     * @param User                      $user
     * @param DockerRegistryCredentials $credentials
     *
     * @throws CredentialsNotFound
     */
    public function remove(User $user, DockerRegistryCredentials $credentials);
}
