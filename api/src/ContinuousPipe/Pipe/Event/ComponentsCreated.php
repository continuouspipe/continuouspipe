<?php

namespace ContinuousPipe\Pipe\Event;

use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\View\ComponentStatus;

class ComponentsCreated implements DeploymentEvent
{
    /**
     * @var DeploymentContext
     */
    private $deploymentContext;

    /**
     * @var \ContinuousPipe\Pipe\View\ComponentStatus[]
     */
    private $componentStatuses;

    /**
     * @param DeploymentContext $deploymentContext
     * @param ComponentStatus[] $componentStatuses
     */
    public function __construct(DeploymentContext $deploymentContext, array $componentStatuses)
    {
        $this->deploymentContext = $deploymentContext;
        $this->componentStatuses = $componentStatuses;
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

    /**
     * @return \ContinuousPipe\Pipe\View\ComponentStatus[]
     */
    public function getComponentStatuses()
    {
        return $this->componentStatuses;
    }
}
