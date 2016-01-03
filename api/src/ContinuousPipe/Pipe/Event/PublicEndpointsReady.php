<?php

namespace ContinuousPipe\Pipe\Event;

use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;

class PublicEndpointsReady implements DeploymentEvent
{
    /**
     * @var DeploymentContext
     */
    private $deploymentContext;

    /**
     * @var PublicEndpoint[]
     */
    private $endpoints;

    /**
     * @param DeploymentContext $deploymentContext
     * @param PublicEndpoint[]  $endpoints
     */
    public function __construct(DeploymentContext $deploymentContext, array $endpoints)
    {
        $this->deploymentContext = $deploymentContext;
        $this->endpoints = $endpoints;
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
     * @return \ContinuousPipe\Pipe\Environment\PublicEndpoint[]
     */
    public function getEndpoints()
    {
        return $this->endpoints;
    }
}
