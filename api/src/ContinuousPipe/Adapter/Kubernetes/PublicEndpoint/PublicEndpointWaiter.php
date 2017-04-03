<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Pipe\DeploymentContext;
use Kubernetes\Client\Model\KubernetesObject;
use React;

interface PublicEndpointWaiter
{
    /**
     * This promise will return a list of `PublicEndpoint` objects.
     *
     * @param React\EventLoop\LoopInterface $loop
     * @param DeploymentContext             $context
     * @param KubernetesObject              $object
     *
     * @throws EndpointNotFound
     *
     * @return React\Promise\PromiseInterface
     */
    public function waitEndpoints(React\EventLoop\LoopInterface $loop, DeploymentContext $context, KubernetesObject $object);
}
