<?php

namespace ContinuousPipe\Security\ApiKey;

use ContinuousPipe\Security\ApiKey\UserApiKey;
use ContinuousPipe\Security\ApiKey\UserApiKeyRepository;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;

class InMemoryUserApiKeyRepository implements UserApiKeyRepository
{
    private $userByKey = [];

    /**
     * {@inheritdoc}
     */
    public function findUserByApiKey(string $key)
    {
        if (array_key_exists($key, $this->userByKey)) {
            return new SecurityUser($this->userByKey[$key]->getUser());
        }

        return null;
    }

    /**
     * @param UserApiKey $key
     */
    public function save(UserApiKey $key)
    {
        $this->userByKey[$key->getApiKey()] = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(string $username)
    {
        return array_values(array_filter($this->userByKey, function (UserApiKey $key) use ($username) {
            return $key->getUser()->getUsername() == $username;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $username, UuidInterface $keyUuid)
    {
        $this->userByKey = array_filter($this->userByKey, function (UserApiKey $key) use ($keyUuid) {
            return $key->getUuid()->toString() != $keyUuid->toString();
        });
    }
}
