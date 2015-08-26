<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use ContinuousPipe\Adapter\Kubernetes\Tests\PublicEndpoint\PredictableServiceWaiter;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;

class ServiceContext implements Context
{
    /**
     * @var PredictableServiceWaiter
     */
    private $serviceWaiter;

    /**
     * @param PredictableServiceWaiter $serviceWaiter
     */
    public function __construct(PredictableServiceWaiter $serviceWaiter)
    {
        $this->serviceWaiter = $serviceWaiter;
    }

    /**
     * @Given the service :name will be created with the public endpoint :address
     */
    public function theServiceWillBeCreatedWithThePublicEndpoint($name, $address)
    {
        $this->serviceWaiter->add(new PublicEndpoint($name, $address));
    }
}
