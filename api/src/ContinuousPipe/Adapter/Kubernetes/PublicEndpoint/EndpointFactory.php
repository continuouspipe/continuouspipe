<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Model\Component\Endpoint;
use Kubernetes\Client\Model\KubernetesObject;

interface EndpointFactory
{
    /**
     * @param Component $component
     * @param Endpoint  $endpoint
     *
     * @return KubernetesObject[]
     */
    public function createObjectsFromEndpoint(Component $component, Endpoint $endpoint);
}
