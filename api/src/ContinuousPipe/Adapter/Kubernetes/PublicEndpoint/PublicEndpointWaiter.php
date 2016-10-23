<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Model\KubernetesObject;
use LogStream\Log;
use React;

interface PublicEndpointWaiter
{
    /**
     * @param React\EventLoop\LoopInterface $loop
     * @param DeploymentContext             $context
     * @param KubernetesObject              $object
     * @param Log                           $log
     *
     * @throws EndpointNotFound
     *
     * @return PublicEndpoint
     */
    public function waitEndpoint(React\EventLoop\LoopInterface $loop, DeploymentContext $context, KubernetesObject $object, Log $log);
}
