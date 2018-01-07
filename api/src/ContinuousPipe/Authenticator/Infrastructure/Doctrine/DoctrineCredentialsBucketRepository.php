<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Doctrine;

use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DoctrineCredentialsBucketRepository implements BucketRepository
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
    public function find(UuidInterface $uuid)
    {
        if ($bucket = $this->getRepository()->find((string) $uuid)) {
            return $bucket;
        }

        throw new BucketNotFound(sprintf(
            'Bucket with UUID %s is not found',
            $uuid
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function save(Bucket $bucket)
    {
        $this->entityManager->persist($bucket);
        $this->entityManager->flush();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository()
    {
        return $this->entityManager->getRepository(Bucket::class);
    }
}
