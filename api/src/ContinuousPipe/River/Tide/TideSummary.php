<?php

namespace ContinuousPipe\River\Tide;

use ContinuousPipe\River\Tide\Summary\CurrentTask;
use ContinuousPipe\River\Tide\Summary\DeployedService;

class TideSummary
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var DeployedService[]
     */
    private $deployedServices;

    /**
     * @var CurrentTask
     */
    private $currentTask;

    /**
     * @param string            $status
     * @param DeployedService[] $deployedServices
     * @param CurrentTask       $currentTask
     */
    public function __construct($status, array $deployedServices = [], CurrentTask $currentTask = null)
    {
        $this->status = $status;
        $this->deployedServices = $deployedServices;
        $this->currentTask = $currentTask;
    }
}
