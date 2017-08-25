<?php

namespace ContinuousPipe\River\Managed\Resources\History;

interface ResourceUsageHistoryRepository
{
    public function save(ResourceUsageHistory $entry);
}
