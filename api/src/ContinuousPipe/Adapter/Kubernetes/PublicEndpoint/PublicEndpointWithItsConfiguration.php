<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Model\Component\Endpoint;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;

class PublicEndpointWithItsConfiguration extends PublicEndpoint
{
    /**
     * @var Endpoint
     */
    private $configuration;

    public static function fromEndpoint(PublicEndpoint $endpoint, Endpoint $configuration) : self
    {
        $endpointWithConfiguration = new self(
            $endpoint->getName(),
            $endpoint->getAddress(),
            $endpoint->getPorts()
        );

        $endpointWithConfiguration->configuration = $configuration;

        return $endpointWithConfiguration;
    }

    /**
     * @return Endpoint
     */
    public function getConfiguration(): Endpoint
    {
        return $this->configuration;
    }
}
