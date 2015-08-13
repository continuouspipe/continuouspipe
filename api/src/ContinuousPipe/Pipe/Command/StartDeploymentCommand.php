<?php

namespace ContinuousPipe\Pipe\Command;

use ContinuousPipe\Pipe\Deployment;

class StartDeploymentCommand
{
    /**
     * @var Deployment
     */
    private $deployment;

    /**
     * @param Deployment $deployment
     */
    public function __construct(Deployment $deployment)
    {
        $this->deployment = $deployment;
    }

    /**
     * @return Deployment
     */
    public function getDeployment()
    {
        return $this->deployment;
    }
}
