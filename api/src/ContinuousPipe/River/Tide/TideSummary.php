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
     * @var string
     */
    private $environment;

    /**
     * @param string $status
     * @param DeployedService[] $deployedServices
     * @param CurrentTask $currentTask
     * @param array $environments
     */
    public function __construct($status, array $deployedServices = [], CurrentTask $currentTask = null, string $environment = null)
    {
        $this->status = $status;
        $this->deployedServices = $deployedServices;
        $this->currentTask = $currentTask;
        $this->environment = $environment;
    }
}
