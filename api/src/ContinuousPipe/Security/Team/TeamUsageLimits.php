<?php

namespace ContinuousPipe\Security\Team;

use JMS\Serializer\Annotation as JMS;

class TeamUsageLimits
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $tidesPerHour;

    public function __construct(int $tidesPerHour)
    {
        $this->tidesPerHour = $tidesPerHour;
    }

    /**
     * @return int
     */
    public function getTidesPerHour(): int
    {
        return $this->tidesPerHour;
    }
}
