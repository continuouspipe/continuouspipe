<?php

namespace ContinuousPipe\Authenticator\Security\ApiKey;

use ContinuousPipe\Authenticator\Security\User\SystemUser;
use Symfony\Component\Security\Core\User\UserInterface;

class SystemUserByApiKey implements UserByApiKeyRepository
{
    /**
     * @var string[]
     */
    private $keys;

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
     * @param string $key
     */
    public function addKey(string $key)
    {
        $this->keys[] = $key;
    }
}
