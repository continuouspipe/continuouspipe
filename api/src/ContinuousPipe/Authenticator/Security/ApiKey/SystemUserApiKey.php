<?php

namespace ContinuousPipe\Authenticator\Security\ApiKey;

use ContinuousPipe\Authenticator\Security\User\SystemUser;
use ContinuousPipe\Security\ApiKey\UserApiKey;
use ContinuousPipe\Security\ApiKey\UserApiKeyRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SystemUserApiKey implements UserApiKeyRepository
{
    /**
     * @var string[]
     */
    private $keys;

    /**
     * @param array $keys
     */
    public function __construct(array $keys = [])
    {
        $this->keys = $keys;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByApiKey(string $key)
    {
        if (in_array($key, $this->keys)) {
            return new SystemUser($key);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(UserApiKey $key)
    {
        throw new \RuntimeException('Unable to save keys for system users');
    }

    /**
     * @param string $key
     */
    public function addKey(string $key)
    {
        $this->keys[] = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(string $username)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $username, UuidInterface $keyUuid)
    {
        throw new \RuntimeException('Unable to delete a system user key');
    }
}
