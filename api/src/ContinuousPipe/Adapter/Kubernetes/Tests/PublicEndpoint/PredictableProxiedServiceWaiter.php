<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\PublicEndpoint;

use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\ProxiedServiceWaiter;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Model\Service;
use LogStream\Log;

class PredictableProxiedServiceWaiter extends ProxiedServiceWaiter
{
    public function __construct(PredictableServiceWaiter $decoratedWaiter)
    {
        $this->decoratedWaiter = $decoratedWaiter;
    }

    /**
     * @param PublicEndpoint $endpoint
     */
    public function add(PublicEndpoint $endpoint)
    {
        $this->decoratedWaiter->add($endpoint);
    }
}
