<?php

namespace ContinuousPipe\River\Managed\Resources;

use ContinuousPipe\River\Managed\Resources\Calculation\Interval;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistory;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistoryRepository;
use Ramsey\Uuid\UuidInterface;

class TracedUsageHistoryRepository implements ResourceUsageHistoryRepository
{
    /**
     * @var ResourceUsageHistory[]
     */
    private $saved = [];

    /**
     * @var ResourceUsageHistoryRepository
     */
    private $decoratedRepository;

    /**
     * @param ResourceUsageHistoryRepository $decoratedRepository
     */
    public function __construct(ResourceUsageHistoryRepository $decoratedRepository)
    {
        $this->decoratedRepository = $decoratedRepository;
    }

    /**
     * @param ResourceUsageHistory $entry
     */
    public function save(ResourceUsageHistory $entry)
    {
        $this->decoratedRepository->save($entry);

        $this->saved[] = $entry;
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlow(UuidInterface $flowUuid): array
    {
        return $this->decoratedRepository->findByFlow($flowUuid);
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlowAndDateInterval(UuidInterface $flowUuid, Interval $interval): array
    {
        return $this->decoratedRepository->findByFlowAndDateInterval($flowUuid, $interval);
    }

    /**
     * @return ResourceUsageHistory[]
     */
    public function getSaved(): array
    {
        return $this->saved;
    }

    public function clearHistory()
    {
        $this->saved = [];
    }
}
