<?php

namespace ContinuousPipe\Pipe\Kubernetes\PublicEndpoint;

use ContinuousPipe\Model\Component\Endpoint;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Model\KubernetesObject;
use React;

class ApplyEndpointTransformationAfterWaiting implements PublicEndpointWaiter
{
    /**
     * @var PublicEndpointWaiter
     */
    private $decoratedWaiter;
    /**
     * @var PublicEndpointTransformer[]
     */
    private $transformers;

    public function __construct(PublicEndpointWaiter $decoratedWaiter, array $transformers)
    {
        $this->decoratedWaiter = $decoratedWaiter;
        $this->transformers = $transformers;
    }

    /**
     * {@inheritdoc}
     */
    public function waitEndpoints(React\EventLoop\LoopInterface $loop, DeploymentContext $context, KubernetesObject $object)
    {
        return $this->decoratedWaiter->waitEndpoints($loop, $context, $object)->then(function (array $foundEndpoints) use ($context, $object) {
            // Create the `PublicEndpointWithItsConfiguration` objects
            $endpointsWithConfiguration = array_map(function (PublicEndpoint $endpoint) use ($context) {
                if (null !== ($configuration = $this->getEndpointConfiguration($context, $endpoint))) {
                    return PublicEndpointWithItsConfiguration::fromEndpoint($endpoint, $configuration);
                }

                return $endpoint;
            }, $foundEndpoints);

            foreach ($this->transformers as $transformer) {
                if ($transformer instanceof PublicEndpointTransformer) {
                    $endpointsWithConfiguration = array_map(function (PublicEndpoint $publicEndpoint) use ($context, $object, $transformer) {
                        if ($publicEndpoint instanceof PublicEndpointWithItsConfiguration) {
                            return $transformer->transform($context, $publicEndpoint, $publicEndpoint->getConfiguration(), $object);
                        }

                        return $publicEndpoint;
                    }, $endpointsWithConfiguration);
                }

                if ($transformer instanceof PublicEndpointCollectionTransformer) {
                    $endpointsWithConfiguration = $transformer->transform($context, $endpointsWithConfiguration, $object);
                }
            }

            return $endpointsWithConfiguration;
        });
    }

    /**
     * @param DeploymentContext $context
     * @param PublicEndpoint $endpoint
     *
     * @return Endpoint|null
     */
    private function getEndpointConfiguration(DeploymentContext $context, PublicEndpoint $endpoint)
    {
        foreach ($context->getDeployment()->getRequest()->getSpecification()->getComponents() as $component) {
            foreach ($component->getEndpoints() as $endpointConfiguration) {
                if ($endpointConfiguration->getName() === $endpoint->getName()) {
                    return $endpointConfiguration;
                }
            }
        }

        return null;
    }
}
