<?php

namespace ContinuousPipe\River\Tide;

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
     * @param string            $status
     * @param DeployedService[] $deployedServices
     */
    public function __construct($status, array $deployedServices = [])
    {
        $this->status = $status;
        $this->deployedServices = $deployedServices;
    }
}
