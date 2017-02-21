<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Pipe\DeploymentContext;
use Kubernetes\Client\Model\KubernetesObject;
use React;

interface PublicEndpointWaiter
{
    /**
     * @param React\EventLoop\LoopInterface $loop
     * @param DeploymentContext             $context
     * @param KubernetesObject              $object
     *
     * @throws EndpointNotFound
     *
     * @return React\Promise\PromiseInterface
     */
    public function waitEndpoint(React\EventLoop\LoopInterface $loop, DeploymentContext $context, KubernetesObject $object);
}
