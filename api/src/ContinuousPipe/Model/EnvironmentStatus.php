<?php

namespace ContinuousPipe\Model;

use ContinuousPipe\Model\Component\Status as ComponentStatus;

class EnvironmentStatus implements Status
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var ComponentStatus[]
     */
    private $componentStatuses;

    /**
     * @param string            $status
     * @param ComponentStatus[] $componentStatuses
     */
    public function __construct($status, array $componentStatuses = [])
    {
        $this->status = $status;
        $this->componentStatuses = $componentStatuses;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
