<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\ProxiedPublicEndpoint;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Model\Service;
use LogStream\Log;

class ProxiedServiceWaiter implements ServiceWaiter
{
    /**
     * @var ServiceWaiter
     */
    protected $decoratedWaiter;

    /**
     * @param ServiceWaiter $decoratedWaiter
     */
    public function __construct(ServiceWaiter $decoratedWaiter)
    {
        $this->decoratedWaiter = $decoratedWaiter;
    }

    /**
     * @param DeploymentContext $context
     * @param Service $service
     * @param Log $log
     *
     * @throws EndpointNotFound
     *
     * @return PublicEndpoint
     */
    public function waitService(DeploymentContext $context, Service $service, Log $log)
    {
        $endpoint = $this->decoratedWaiter->waitService($context, $service, $log);
        $labels = $service->getMetadata()->getLabelsAsAssociativeArray();

        if (isset($labels['com.continuouspipe.http-labs'])) {
            return new ProxiedPublicEndpoint($endpoint->getName(), $endpoint->getAddress());
        }

        return $endpoint;
    }
}