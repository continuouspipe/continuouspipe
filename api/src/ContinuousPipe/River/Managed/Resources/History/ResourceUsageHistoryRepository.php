<?php

namespace ContinuousPipe\River\Managed\Resources\History;

use ContinuousPipe\River\Managed\Resources\Calculation\Interval;
use ContinuousPipe\River\Managed\Resources\ResourcesException;
use Ramsey\Uuid\UuidInterface;

interface ResourceUsageHistoryRepository
{
    /**
     * @param ResourceUsageHistory $entry
     *
     * @throws ResourcesException
     */
    public function save(ResourceUsageHistory $entry);

    /**
     * @param UuidInterface $flowUuid
     *
     * @throws ResourcesException
     *
     * @return ResourceUsageHistory[]
     */
    public function findByFlow(UuidInterface $flowUuid) : array;

    /**
     * @param UuidInterface $flowUuid
     * @param Interval $interval
     *
     * @throws ResourcesException
     *
     * @return ResourceUsageHistory[]
     */
    public function findByFlowAndDateInterval(UuidInterface $flowUuid, Interval $interval) : array;
}
