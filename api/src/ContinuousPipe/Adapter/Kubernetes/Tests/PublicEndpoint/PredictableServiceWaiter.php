<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\PublicEndpoint;

use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\EndpointNotFound;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\ServiceWaiter;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Model\Service;
use LogStream\Log;

class PredictableServiceWaiter implements ServiceWaiter
{
    private $endpoints = [];

    /**
     * {@inheritdoc}
     */
    public function waitService(DeploymentContext $context, Service $service, Log $log)
    {
        $serviceName = $service->getMetadata()->getName();
        if (!array_key_exists($serviceName, $this->endpoints)) {
            throw new EndpointNotFound();
        }

        return $this->endpoints[$serviceName];
    }

    /**
     * @param PublicEndpoint $endpoint
     */
    public function add(PublicEndpoint $endpoint)
    {
        $this->endpoints[$endpoint->getName()] = $endpoint;
    }
}
