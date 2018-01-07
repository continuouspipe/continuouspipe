<?php

namespace ContinuousPipe\Pipe\Kubernetes\PublicEndpoint\Ingress;

use ContinuousPipe\Pipe\Kubernetes\Inspector\ReverseTransformer\ComponentPublicEndpointResolver;
use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\IngressRule;
use Kubernetes\Client\Model\KubernetesObject;

class IngressEndpointResolver implements ComponentPublicEndpointResolver
{
    /**
     * {@inheritdoc}
     */
    public function resolve(KubernetesObject $serviceOrIngress): array
    {
        if (!$serviceOrIngress instanceof Ingress) {
            return [];
        }

        if (!is_array($rules = $serviceOrIngress->getSpecification()->getRules())) {
            return [];
        }

        return array_map(function (IngressRule $rule) {
            return $rule->getHost();
        }, $rules);
    }
}
