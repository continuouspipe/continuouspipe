<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;

class ServiceContext implements Context
{
    /**
     * @When the service :name is created with the public endpoint :address
     */
    public function theServiceIsCreatedWithThePublicEndpoint($name, $address)
    {

    }
}
