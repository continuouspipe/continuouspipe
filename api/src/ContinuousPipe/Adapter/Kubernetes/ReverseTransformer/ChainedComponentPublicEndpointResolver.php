<?php

namespace ContinuousPipe\Adapter\Kubernetes\ReverseTransformer;

use Kubernetes\Client\Model\KubernetesObject;

class ChainedComponentPublicEndpointResolver implements ComponentPublicEndpointResolver
{
    /**
     * @var ComponentPublicEndpointResolver[]
     */
    private $resolvers;

    public function __construct(array $resolvers)
    {
        foreach ($resolvers as $resolver) {
            if ($resolver instanceof ComponentPublicEndpointResolver) {
                $this->resolvers[] = $resolver;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(KubernetesObject $serviceOrIngress)
    {
        foreach ($this->resolvers as $resolver) {
            if ($endpoint = $resolver->resolve($serviceOrIngress)) {
                return $endpoint;
            }
        }
    }
}
