<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Doctrine;

use ContinuousPipe\Authenticator\Security\ApiKey\UserApiKey;
use ContinuousPipe\Authenticator\Security\ApiKey\UserByApiKeyRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\User\UserInterface;

class DoctrineUserByApiKeyRepository implements UserByApiKeyRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $key
     *
     * @return UserInterface|null
     */
    public function findUserByApiKey(string $key)
    {
        // TODO: Implement findUserByApiKey() method.
    }

    /**
     * @param UserApiKey $key
     */
    public function save(UserApiKey $key)
    {
        // TODO: Implement save() method.
    }
}
