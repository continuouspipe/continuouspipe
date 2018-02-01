<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Repository\FilterHash;

use ContinuousPipe\River\Filter\FilterHash\FilterHash;
use ContinuousPipe\River\Filter\FilterHash\FilterHashRepository;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\UuidInterface;

class DoctrineFilterHashRepository implements FilterHashRepository
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
    public function findByTideUuid(UuidInterface $uuid)
    {
        return $this->entityManager->getRepository(FilterHash::class)->find($uuid->toString());
    }

    /**
     * {@inheritdoc}
     */
    public function save(FilterHash $filterHash)
    {
        $this->entityManager->persist($filterHash);
        $this->entityManager->flush();

        return $filterHash;
    }
}
