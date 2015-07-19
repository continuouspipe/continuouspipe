<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Doctrine;

use ContinuousPipe\Authenticator\CredentialsNotFound;
use ContinuousPipe\Authenticator\DockerRegistryCredentialsRepository;
use ContinuousPipe\Authenticator\Infrastructure\Doctrine\Entity\UserDockerRegistryCredentialsDto;
use ContinuousPipe\User\DockerRegistryCredentials;
use ContinuousPipe\User\User;
use Doctrine\ORM\EntityManager;

class DoctrineDockerRegistryCredentialsRepository implements DockerRegistryCredentialsRepository
{
    const DTO_CLASS = 'ContinuousPipe\Authenticator\Infrastructure\Doctrine\Entity\UserDockerRegistryCredentialsDto';

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
    public function findOneByUserAndServer(User $user, $serverAddress)
    {
        return $this->getDtoByUserAndServerAddress($user, $serverAddress)->credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(User $user)
    {
        $credentialsDtos = $this->entityManager->getRepository(self::DTO_CLASS)->findBy([
            'userUsername' => $user->getEmail(),
        ]);

        return array_map(function (UserDockerRegistryCredentialsDto $dto) {
            return $dto->credentials;
        }, $credentialsDtos);
    }

    /**
     * {@inheritdoc}
     */
    public function save(DockerRegistryCredentials $credentials, User $user)
    {
        try {
            $dto = $this->getDtoByUserAndServerAddress($user, $credentials->getServerAddress());
        } catch (CredentialsNotFound $e) {
            $dto = new UserDockerRegistryCredentialsDto();
            $dto->userUsername = $user->getEmail();
        }

        $dto->credentials = $credentials;

        $this->entityManager->persist($dto);
        $this->entityManager->flush();
    }

    /**
     * @param User   $user
     * @param string $serverAddress
     *
     * @return UserDockerRegistryCredentialsDto
     *
     * @throws CredentialsNotFound
     */
    private function getDtoByUserAndServerAddress(User $user, $serverAddress)
    {
        $credentials = $this->entityManager->getRepository(self::DTO_CLASS)->findOneBy([
            'userUsername' => $user->getEmail(),
            'credentials.serverAddress' => $serverAddress,
        ]);

        if (null === $credentials) {
            throw new CredentialsNotFound();
        }

        return $credentials;
    }
}
