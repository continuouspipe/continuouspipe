<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

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
    public function waitEndpoint(React\EventLoop\LoopInterface $loop, DeploymentContext $context, KubernetesObject $object)
    {
        return $this->decoratedWaiter->waitEndpoint($loop, $context, $object)->then(function (PublicEndpoint $endpoint) use ($context, $object) {
            if (null === ($configuration = $this->getEndpointConfiguration($context, $endpoint))) {
                return $endpoint;
            }

            foreach ($this->transformers as $transformer) {
                $endpoint = $transformer->transform($context, $endpoint, $configuration, $object);
            }

            return $endpoint;
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
