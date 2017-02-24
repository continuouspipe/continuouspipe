<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Doctrine;

use ContinuousPipe\Authenticator\Security\ApiKey\UserApiKey;
use ContinuousPipe\Authenticator\Security\ApiKey\UserApiKeyRepository;
use ContinuousPipe\Security\User\SecurityUser;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DoctrineUserApiKeyRepository implements UserApiKeyRepository
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
     * {@inheritdoc}
     */
    public function findUserByApiKey(string $key)
    {
        $key = $this->entityManager->getRepository(UserApiKey::class)->findOneBy([
            'apiKey' => $key
        ]);

        if (null === $key) {
            return null;
        }

        return new SecurityUser($key->getUser());
    }

    /**
     * {@inheritdoc}
     */
    public function save(UserApiKey $key)
    {
        $this->entityManager->persist($key);
        $this->entityManager->flush($key);
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(string $username)
    {
        return $this->entityManager->getRepository(UserApiKey::class)->findBy([
            'user' => $username,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $username, UuidInterface $keyUuid)
    {
        $keys = $this->entityManager->getRepository(UserApiKey::class)->findBy([
            'uuid' => $keyUuid,
            'user' => $username
        ]);

        if (count($keys) !== 1) {
            throw new \InvalidArgumentException(sprintf(
                'Found %d api keys matching this user and UUUD',
                count($keys)
            ));
        }

        $this->entityManager->remove($keys[0]);
        $this->entityManager->flush($keys[0]);
    }
}
