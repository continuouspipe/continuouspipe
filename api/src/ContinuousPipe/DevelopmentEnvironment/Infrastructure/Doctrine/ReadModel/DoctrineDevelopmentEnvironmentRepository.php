<?php

namespace ContinuousPipe\DevelopmentEnvironment\Infrastructure\Doctrine\ReadModel;

use ContinuousPipe\DevelopmentEnvironment\ReadModel\DevelopmentEnvironment;
use ContinuousPipe\DevelopmentEnvironment\ReadModel\DevelopmentEnvironmentNotFound;
use ContinuousPipe\DevelopmentEnvironment\ReadModel\DevelopmentEnvironmentRepository;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\UuidInterface;

class DoctrineDevelopmentEnvironmentRepository implements DevelopmentEnvironmentRepository
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

    public function findByFlow(UuidInterface $flowUuid): array
    {
        return $this->entityManager->getRepository(DevelopmentEnvironment::class)
            ->findBy([
                'flowUuid' => $flowUuid
            ])
        ;
    }

    public function save(DevelopmentEnvironment $developmentEnvironment)
    {
        $developmentEnvironment = $this->entityManager->merge($developmentEnvironment);

        $this->entityManager->persist($developmentEnvironment);
        $this->entityManager->flush();
    }

    public function find(UuidInterface $uuid): DevelopmentEnvironment
    {
        $developmentEnvironment = $this->entityManager->getRepository(DevelopmentEnvironment::class)
            ->findOneBy([
                'uuid' => $uuid
            ])
        ;

        if (null === $developmentEnvironment) {
            throw new DevelopmentEnvironmentNotFound('Development environment not found');
        }

        return $developmentEnvironment;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(UuidInterface $uuid)
    {
        $environment = $this->find($uuid);

        $this->entityManager->remove($environment);
        $this->entityManager->flush();
    }
}
