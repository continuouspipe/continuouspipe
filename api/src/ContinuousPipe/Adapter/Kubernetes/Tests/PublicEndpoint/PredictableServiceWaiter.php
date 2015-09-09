<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\PublicEndpoint;

use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\EndpointNotFound;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\ServiceWaiter;
use ContinuousPipe\Adapter\Kubernetes\Service\Service;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use LogStream\Log;

class PredictableServiceWaiter implements ServiceWaiter
{
    private $endpoints = [];

    /**
     * {@inheritdoc}
     */
    public function waitService(DeploymentContext $context, Service $service, Log $log)
    {
        $serviceName = $service->getService()->getMetadata()->getName();
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
