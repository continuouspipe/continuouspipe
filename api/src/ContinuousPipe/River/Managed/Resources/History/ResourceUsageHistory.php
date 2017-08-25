<?php

namespace ContinuousPipe\River\Managed\Resources\History;

use ContinuousPipe\River\Managed\Resources\ResourceUsage;
use Ramsey\Uuid\UuidInterface;

class ResourceUsageHistory
{
    /**
     * @var UuidInterface
     */
    private $entryUuid;

    /**
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @var string
     */
    private $environmentIdentifier;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var ResourceUsage
     */
    private $resourcesUsage;

    public function __construct(
        UuidInterface $entryUuid,
        UuidInterface $flowUuid,
        string $environmentIdentifier,
        ResourceUsage $resourcesUsage,
        \DateTimeInterface $dateTime
    ) {
        $this->entryUuid = $entryUuid;
        $this->flowUuid = $flowUuid;
        $this->environmentIdentifier = $environmentIdentifier;
        $this->resourcesUsage = $resourcesUsage;
        $this->dateTime = $dateTime;
    }

    /**
     * @return UuidInterface
     */
    public function getEntryUuid(): UuidInterface
    {
        return $this->entryUuid;
    }

    /**
     * @return UuidInterface
     */
    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }

    /**
     * @return string
     */
    public function getEnvironmentIdentifier(): string
    {
        return $this->environmentIdentifier;
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
