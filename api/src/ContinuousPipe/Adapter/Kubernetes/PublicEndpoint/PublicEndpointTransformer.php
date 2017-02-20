<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Model\Component\Endpoint;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Model\KubernetesObject;

interface PublicEndpointTransformer
{
    /**
     * Transform the given public-endpoint.
     *
     * @param DeploymentContext $deploymentContext
     * @param PublicEndpoint $publicEndpoint
     * @param Endpoint $endpointConfiguration
     * @param KubernetesObject $object
     *
     * @return PublicEndpoint
     */
    public function transform(
        DeploymentContext $deploymentContext,
        PublicEndpoint $publicEndpoint,
        Endpoint $endpointConfiguration,
        KubernetesObject $object
    ) : PublicEndpoint;
}
