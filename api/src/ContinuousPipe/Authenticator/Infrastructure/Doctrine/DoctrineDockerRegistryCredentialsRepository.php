<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Doctrine;

use ContinuousPipe\Authenticator\CredentialsNotFound;
use ContinuousPipe\Authenticator\DockerRegistryCredentialsRepository;
use ContinuousPipe\User\User;
use Doctrine\ORM\EntityManager;

class DoctrineDockerRegistryCredentialsRepository implements DockerRegistryCredentialsRepository
{
    const DTO_CLASS = 'ContinuousPipe\Authenticator\Infrastructure\Doctrine\Entity\UserDockerRegistryCredentialsDto';

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUserAndServer(User $user, $serverAddress)
    {
        $credentials = $this->entityManager->getRepository(self::DTO_CLASS)->findOneBy([
            'userUsername' => $user->getEmail(),
            'credentials.serverAddress' => $serverAddress
        ]);

        if (null === $credentials) {
            throw new CredentialsNotFound();
        }

        return $credentials->credentials;
    }
}
