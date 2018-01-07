<?php

namespace ContinuousPipe\River\Managed\Resources\Calculation;

use ContinuousPipe\River\Managed\Resources\ResourceUsage;

class UsageSnapshot
{
    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var ResourceUsage
     */
    private $usage;

    /**
     * @param \DateTimeInterface $dateTime
     * @param ResourceUsage $usage
     */
    public function __construct(\DateTimeInterface $dateTime, ResourceUsage $usage)
    {
        $this->dateTime = $dateTime;
        $this->usage = $usage;
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
    public function getUsage(): ResourceUsage
    {
        return $this->usage;
    }
}
