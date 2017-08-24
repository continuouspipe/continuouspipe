<?php

namespace ContinuousPipe\River\Managed\Resources;

use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistory;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistoryRepository;

class InMemoryUsageHistoryRepository implements ResourceUsageHistoryRepository
{
    private $entries = [];

    public function save(ResourceUsageHistory $entry)
    {
        $this->entries[] = $entry;
    }
}
