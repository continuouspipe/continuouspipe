<?php

namespace ContinuousPipe\River\Tide\Summary;

use ContinuousPipe\Pipe\Client\ComponentStatus;
use ContinuousPipe\Pipe\Client\PublicEndpoint;

class DeployedService
{
    /**
     * @var ComponentStatus
     */
    private $status;

    /**
     * @var PublicEndpoint
     */
    private $publicEndpoint;

    /**
     * @param ComponentStatus $status
     * @param PublicEndpoint  $publicEndpoint
     */
    public function __construct(ComponentStatus $status, PublicEndpoint $publicEndpoint = null)
    {
        $this->status = $status;
        $this->publicEndpoint = $publicEndpoint;
    }
}
