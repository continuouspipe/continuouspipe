<?php

namespace ContinuousPipe\Pipe\Command;

use ContinuousPipe\Message\Message;
use ContinuousPipe\Pipe\View\Deployment;

class StartDeploymentCommand implements Message
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
