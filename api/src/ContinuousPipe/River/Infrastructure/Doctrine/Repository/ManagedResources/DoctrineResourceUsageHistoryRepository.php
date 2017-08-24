<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Repository\ManagedResources;

use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistory;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistoryRepository;
use Doctrine\ORM\EntityManager;

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
}
