<?php

namespace ContinuousPipe\Authenticator\Tests;

use ContinuousPipe\Authenticator\CredentialsNotFound;
use ContinuousPipe\Authenticator\DockerRegistryCredentialsRepository;
use ContinuousPipe\Security\User\User;

class InMemoryDockerRegistryCredentialsRepository implements DockerRegistryCredentialsRepository
{
    /**
     * @var array
     */
    private $credentials = [];

    /**
     * {@inheritdoc}
     */
    public function findOneByUserAndServer(User $user, $serverAddress)
    {
        $credentials = array_filter($this->findByUser($user), function (DockerRegistryCredentials $credentials) use ($serverAddress) {
            return $credentials->getServerAddress() == $serverAddress;
        });

        if (count($credentials) == 0) {
            throw new CredentialsNotFound();
        }

        return current($credentials);
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(User $user)
    {
        if (!array_key_exists($user->getEmail(), $this->credentials)) {
            return [];
        }

        return $this->credentials[$user->getEmail()];
    }

    /**
     * {@inheritdoc}
     */
    public function save(DockerRegistryCredentials $credentials, User $user)
    {
        if (!array_key_exists($user->getEmail(), $this->credentials)) {
            $this->credentials[$user->getEmail()] = [];
        }

        $this->credentials[$user->getEmail()][] = $credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(User $user, DockerRegistryCredentials $credentials)
    {
        if (!array_key_exists($user->getEmail(), $this->credentials)) {
            throw new CredentialsNotFound(sprintf('No credentials found for user "%s"', $user->getEmail()));
        }

        $this->credentials[$user->getEmail()] = array_values(array_filter($this->credentials[$user->getEmail()], function ($found) use ($credentials) {
            return $found != $credentials;
        }));
    }
}
