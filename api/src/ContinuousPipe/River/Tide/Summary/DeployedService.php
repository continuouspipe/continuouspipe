<?php

namespace ContinuousPipe\River\Tide\Summary;

use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\Pipe\View\ComponentStatus;

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
    public function __construct(ComponentStatus $status = null, PublicEndpoint $publicEndpoint = null)
    {
        $this->status = $status;
        $this->publicEndpoint = $publicEndpoint;
    }
}
