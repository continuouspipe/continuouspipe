<?php

namespace ContinuousPipe\Pipe\Kubernetes\PublicEndpoint\Ingress\Transformer;

use ContinuousPipe\Pipe\Kubernetes\PublicEndpoint\PublicEndpointCollectionTransformer;
use ContinuousPipe\Pipe\Kubernetes\PublicEndpoint\PublicEndpointWithItsConfiguration;
use ContinuousPipe\Pipe\DeploymentContext;
use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\KubernetesObject;

class UsesIngressRulesAsEndpoint implements PublicEndpointCollectionTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform(
        DeploymentContext $deploymentContext,
        array $publicEndpoints,
        KubernetesObject $object
    ): array {
        if (!$object instanceof Ingress || 0 === count($rules = $object->getSpecification()->getRules())) {
            return $publicEndpoints;
        }

        foreach ($publicEndpoints as $endpointKey => $publicEndpoint) {
            /** @var PublicEndpointWithItsConfiguration $publicEndpoint */
            if ($publicEndpoint->getConfiguration()->getName() == $object->getMetadata()->getName()) {
                unset($publicEndpoints[$endpointKey]);

                foreach ($object->getSpecification()->getRules() as $rule) {
                    $publicEndpoints[] = $publicEndpoint->withAddress($rule->getHost());
                }
            }
        }

        return array_values($publicEndpoints);
    }
}
