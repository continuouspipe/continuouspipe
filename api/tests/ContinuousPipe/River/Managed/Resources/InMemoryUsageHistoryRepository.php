<?php

namespace ContinuousPipe\River\Managed\Resources;

use ContinuousPipe\River\Managed\Resources\Calculation\Interval;
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
        $entries = array_values(array_filter($this->entries, function(ResourceUsageHistory $entry) use ($flowUuid) {
            return $entry->getFlowUuid()->equals($flowUuid);
        }));

        usort($entries, function (ResourceUsageHistory $left, ResourceUsageHistory $right) {
            return $left->getDateTime() > $right->getDateTime() ? 1 : -1;
        });

        return $entries;
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlowAndDateInterval(UuidInterface $flowUuid, Interval $interval): array
    {
        return array_values(array_filter($this->findByFlow($flowUuid), function(ResourceUsageHistory $entry) use ($interval) {
            return $entry->getDateTime() >= $interval->getLeft() && $entry->getDateTime() <= $interval->getRight();
        }));
    }
}
