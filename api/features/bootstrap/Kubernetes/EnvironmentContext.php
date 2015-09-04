<?php

namespace Kubernetes;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class EnvironmentContext implements Context
{
    /**
     * @var \EnvironmentContext
     */
    private $environmentContext;

    /**
     * @var \ProviderContext
     */
    private $providerContext;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->environmentContext = $scope->getEnvironment()->getContext('EnvironmentContext');
        $this->providerContext = $scope->getEnvironment()->getContext('ProviderContext');
    }

    /**
     * @Given I have the application :name deployed
     */
    public function iHaveTheApplicationDeployed($name)
    {
        $this->environmentContext->sendDeploymentRequest('kubernetes/'.ProviderContext::DEFAULT_PROVIDER_NAME, $name);
    }

    /**
     * @When I request the environment list of the Kubernetes provider
     */
    public function iRequestTheEnvironmentListOfTheKubernetesProvider()
    {
        $this->providerContext->iRequestTheEnvironmentListOfProvider(ProviderContext::DEFAULT_PROVIDER_NAME, 'kubernetes');
    }
}
