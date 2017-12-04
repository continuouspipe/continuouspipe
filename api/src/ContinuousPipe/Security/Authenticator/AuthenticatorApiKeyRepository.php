<?php

namespace ContinuousPipe\Security\Authenticator;

use ContinuousPipe\Security\ApiKey\UserApiKey;
use ContinuousPipe\Security\ApiKey\UserApiKeyRepository;
use ContinuousPipe\Security\User\SecurityUser;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticatorApiKeyRepository implements UserApiKeyRepository
{
    /**
     * @var AuthenticatorClient
     */
    private $authenticatorClient;

    /**
     * @param AuthenticatorClient $authenticatorClient
     */
    public function __construct(AuthenticatorClient $authenticatorClient)
    {
        $this->authenticatorClient = $authenticatorClient;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByApiKey(string $key)
    {
        if (null !== ($user = $this->authenticatorClient->findUserByApiKey($key))) {
            return new SecurityUser($user);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(UserApiKey $key)
    {
        throw new \RuntimeException('Unable to find an API key by username through Authenticator\'s API SDK');
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(string $username)
    {
        throw new \RuntimeException('Unable to find an API key by username through Authenticator\'s API SDK');
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $username, UuidInterface $keyUuid)
    {
        throw new \RuntimeException('Unable to delete an API key through Authenticator\'s API');
    }
}
