<?php

namespace ContinuousPipe\River\Managed\Resources\History;

use ContinuousPipe\Model\Component\Resources;
use ContinuousPipe\River\Managed\Resources\ResourceUsage;
use Ramsey\Uuid\UuidInterface;

class ResourceUsageHistoryEntry
{
    /**
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var ResourceUsage
     */
    private $resourcesUsage;

    /**
     * @param UuidInterface $flowUuid
     * @param \DateTimeInterface $dateTime
     * @param ResourceUsage $resourcesUsage
     */
    public function __construct(UuidInterface $flowUuid, \DateTimeInterface $dateTime, ResourceUsage $resourcesUsage)
    {
        $this->flowUuid = $flowUuid;
        $this->dateTime = $dateTime;
        $this->resourcesUsage = $resourcesUsage;
    }

    /**
     * @return UuidInterface
     */
    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDateTime(): \DateTimeInterface
    {
        return $this->dateTime;
    }

    /**
     * @return ResourceUsage
     */
    public function getResourcesUsage(): ResourceUsage
    {
        return $this->resourcesUsage;
    }
}
