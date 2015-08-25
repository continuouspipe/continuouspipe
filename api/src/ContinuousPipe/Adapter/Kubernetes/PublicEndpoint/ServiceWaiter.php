<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Model\Service;

interface ServiceWaiter
{
    /**
     * @param DeploymentContext $context
     * @param Service           $service
     *
     * @throws EndpointNotFound
     *
     * @return PublicEndpoint
     */
    public function waitService(DeploymentContext $context, Service $service);
}
