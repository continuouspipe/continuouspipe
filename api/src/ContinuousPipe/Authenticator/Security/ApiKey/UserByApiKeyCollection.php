<?php

namespace ContinuousPipe\Authenticator\Security\ApiKey;

use Symfony\Component\Security\Core\User\UserInterface;

class UserByApiKeyCollection implements UserByApiKeyRepository
{
    /**
     * @var array|UserByApiKeyRepository[]
     */
    private $repositories;

    /**
     * @param UserByApiKeyRepository[] $repositories
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
    public function save(UserApiKey $key)
    {
        throw new \RuntimeException('Unable to save ApiKey across multiple repositories');
    }
}
