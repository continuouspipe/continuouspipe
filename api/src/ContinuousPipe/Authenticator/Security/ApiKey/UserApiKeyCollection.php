<?php

namespace ContinuousPipe\Authenticator\Security\ApiKey;

use ContinuousPipe\Security\ApiKey\UserApiKey;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserApiKeyCollection implements UserApiKeyRepository
{
    /**
     * @var array|UserApiKeyRepository[]
     */
    private $repositories;

    /**
     * @param UserApiKeyRepository[] $repositories
     */
    public function __construct(array $repositories)
    {
        $this->repositories = $repositories;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByApiKey(string $key)
    {
        foreach ($this->repositories as $repository) {
            if (null !== ($user = $repository->findUserByApiKey($key))) {
                return $user;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(string $username)
    {
        return array_reduce($this->repositories, function (array $carry, UserApiKeyRepository $repository) use ($username) {
            return array_merge($carry, $repository->findByUser($username));
        }, []);
    }

    /**
     * {@inheritdoc}
     */
    public function save(UserApiKey $key)
    {
        throw new \RuntimeException('Unable to save ApiKey across multiple repositories');
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $username, UuidInterface $keyUuid)
    {
        throw new \RuntimeException('Unable to delete ApiKey across multiple repositories');
    }
}
