<?php

namespace ContinuousPipe\Pipe\Command;

use ContinuousPipe\Pipe\View\Deployment;
use JMS\Serializer\Annotation as JMS;

class StartDeploymentCommand
{
    /**
     * @JMS\Type("ContinuousPipe\Pipe\View\Deployment")
     *
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
