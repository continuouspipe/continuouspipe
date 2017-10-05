<?php

namespace ContinuousPipe\Pipe\Command;

use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;

class ProxyPublicEndpointsCommand implements DeploymentCommand
{
    /**
     * @var DeploymentContext
     */
    private $context;

    /**
     * @var PublicEndpoint[]
     */
    private $endpoints;

    /**
     * @param DeploymentContext $context
     * @param PublicEndpoint[]  $endpoints
     */
    public function __construct(DeploymentContext $context, array $endpoints)
    {
        $this->context = $context;
        $this->endpoints = $endpoints;
    }

    /**
     * @return \ContinuousPipe\Pipe\Environment\PublicEndpoint[]
     */
    public function getEndpoints()
    {
        return $this->endpoints;
    }

    /**
     * @return DeploymentContext
     */
    public function getContext()
    {
        return $this->context;
    }
}
