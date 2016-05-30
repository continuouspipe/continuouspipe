<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Model\KubernetesObject;
use LogStream\Log;

interface PublicEndpointWaiter
{
    /**
     * @param DeploymentContext $context
     * @param KubernetesObject  $object
     * @param Log               $log
     *
     * @throws EndpointNotFound
     *
     * @return PublicEndpoint
     */
    public function waitEndpoint(DeploymentContext $context, KubernetesObject $object, Log $log);
}
