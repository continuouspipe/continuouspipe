<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Model\Service;
use LogStream\Log;

interface ServiceWaiter
{
    /**
     * @param DeploymentContext $context
     * @param Service           $service
     * @param Log               $log
     *
     * @throws EndpointNotFound
     *
     * @return PublicEndpoint
     */
    public function waitService(DeploymentContext $context, Service $service, Log $log);
}
