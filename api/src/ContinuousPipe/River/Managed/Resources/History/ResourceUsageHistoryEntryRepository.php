<?php

namespace ContinuousPipe\River\Managed\Resources\History;

interface ResourceUsageHistoryEntryRepository
{
    public function save(ResourceUsageHistoryEntry $entry);
}
