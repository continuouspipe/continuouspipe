<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Repository\ManagedResources;

use ContinuousPipe\River\Managed\Resources\Calculation\Interval;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistory;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistoryRepository;
use ContinuousPipe\River\Managed\Resources\ResourcesException;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\UuidInterface;

class DoctrineResourceUsageHistoryRepository implements ResourceUsageHistoryRepository
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

    public function save(ResourceUsageHistory $entry)
    {
        $this->entityManager->persist($entry);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlow(UuidInterface $flowUuid): array
    {
        return $this->entityManager->getRepository(ResourceUsageHistory::class)->findBy([
            'flowUuid' => $flowUuid,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlowAndDateInterval(UuidInterface $flowUuid, Interval $interval): array
    {
        return $this->entityManager->getRepository(ResourceUsageHistory::class)
            ->createQueryBuilder('h')
            ->where(
                'h.flowUuid = :flowUuid',
                'h.dateTime BETWEEN :left AND :right'
            )
            ->setParameters([
                'flowUuid' => $flowUuid->toString(),
                'left' => $interval->getLeft(),
                'right' => $interval->getRight(),
            ])
            ->getQuery()
            ->getResult();
    }
}
