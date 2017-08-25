<?php

namespace ContinuousPipe\River\Managed\Resources;

use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistory;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistoryRepository;

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
     * @return ResourceUsageHistory[]
     */
    public function getSaved(): array
    {
        return $this->saved;
    }
}
