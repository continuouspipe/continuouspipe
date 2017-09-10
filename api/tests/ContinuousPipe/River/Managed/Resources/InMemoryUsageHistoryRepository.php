<?php

namespace ContinuousPipe\River\Managed\Resources;

use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistory;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistoryRepository;
use Ramsey\Uuid\UuidInterface;

class InMemoryUsageHistoryRepository implements ResourceUsageHistoryRepository
{
    private $entries = [];

    public function save(ResourceUsageHistory $entry)
    {
        $this->entries[] = $entry;
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlow(UuidInterface $flowUuid): array
    {
        return array_values(array_filter($this->entries, function(ResourceUsageHistory $entry) use ($flowUuid) {
            return $entry->getFlowUuid()->equals($flowUuid);
        }));
    }
}
