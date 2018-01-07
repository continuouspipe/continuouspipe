<?php

namespace ContinuousPipe\Pipe\Event;

use ContinuousPipe\Pipe\DeploymentContext;

class EnvironmentPrepared implements DeploymentEvent
{
    /**
     * @var DeploymentContext
     */
    private $deploymentContext;

    /**
     * @param DeploymentContext $deploymentContext
     */
    public function __construct(DeploymentContext $deploymentContext)
    {
        $this->deploymentContext = $deploymentContext;
    }

    /**
     * @return DeploymentContext
     */
    public function getDeploymentContext()
    {
        return $this->deploymentContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeploymentUuid()
    {
        return $this->deploymentContext->getDeployment()->getUuid();
    }
}
