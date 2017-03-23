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
            if (!$resolver instanceof ComponentPublicEndpointResolver) {
                throw new \ErrorException(
                    sprintf('The class "%s" must implement "%s"', get_class($resolver), ComponentPublicEndpointResolver::class)
                );
            }
            $this->resolvers[] = $resolver;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(KubernetesObject $serviceOrIngress) : array
    {
        foreach ($this->resolvers as $resolver) {
            if ($endpoints = $resolver->resolve($serviceOrIngress)) {
                return $endpoints;
            }
        }
        return [];
    }
}
