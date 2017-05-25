<?php

namespace ContinuousPipe\River\Tide;

use ContinuousPipe\River\Tide\Summary\CurrentTask;
use ContinuousPipe\River\Tide\Summary\DeployedService;
use ContinuousPipe\River\Tide\Summary\Environment;

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
     * @param Environment $environment
     */
    public function __construct($status, array $deployedServices = [], CurrentTask $currentTask = null, Environment $environment = null)
    {
        $this->status = $status;
        $this->deployedServices = $deployedServices;
        $this->currentTask = $currentTask;
        $this->environment = $environment;
    }
}
