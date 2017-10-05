<?php

namespace ContinuousPipe\Pipe\Kubernetes\PublicEndpoint;

use ContinuousPipe\Model\Component\Endpoint;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Model\KubernetesObject;

interface PublicEndpointCollectionTransformer
{
    /**
     * Transform the given public-endpoint.
     *
     * @param DeploymentContext $deploymentContext
     * @param PublicEndpointWithItsConfiguration[] $publicEndpoints
     * @param KubernetesObject $object
     *
     * @throws EndpointException
     *
     * @return PublicEndpoint[]
     */
    public function transform(
        DeploymentContext $deploymentContext,
        array $publicEndpoints,
        KubernetesObject $object
    ) : array;
}
