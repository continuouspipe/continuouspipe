<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\User\Authenticator\AuthenticatorClient;
use ContinuousPipe\User\User;

class AuthenticatorCredentialsRepository implements CredentialsRepository
{
    /**
     * @var AuthenticatorClient
     */
    private $authenticatorClient;
    /**
     * @var RegistryServerResolver
     */
    private $registryServerResolver;

    /**
     * @param AuthenticatorClient    $authenticatorClient
     * @param RegistryServerResolver $registryServerResolver
     */
    public function __construct(AuthenticatorClient $authenticatorClient, RegistryServerResolver $registryServerResolver)
    {
        $this->authenticatorClient = $authenticatorClient;
        $this->registryServerResolver = $registryServerResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function findByImage(Image $image, User $user)
    {
        $server = $this->registryServerResolver->getServerName($image);

        $dockerRegistryCredentials = $this->authenticatorClient->getDockerCredentialsByUserEmailAndServer($user->getEmail(), $server);

        return RegistryCredentials::fromDockerRegistryCredentials($dockerRegistryCredentials);
    }
}
