<?php

namespace ContinuousPipe\Security\ApiKey;

use ContinuousPipe\Authenticator\Security\ApiKey\UserApiKey;
use ContinuousPipe\Authenticator\Security\ApiKey\UserByApiKeyRepository;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;

class InMemoryUserApiKeyRepository implements UserByApiKeyRepository
{
    private $userByKey = [];

    /**
     * {@inheritdoc}
     */
    public function findUserByApiKey(string $key)
    {
        if (array_key_exists($key, $this->userByKey)) {
            return new SecurityUser($this->userByKey[$key]);
        }

        return null;
    }

    /**
     * @param UserApiKey $key
     */
    public function save(UserApiKey $key)
    {
        $this->userByKey[$key->getApiKey()] = $key->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(string $username)
    {
        return array_keys(array_filter($this->userByKey, function(User $user) use ($username) {
            return $user->getUsername() == $username;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $username, UuidInterface $keyUuid)
    {
    }
}
