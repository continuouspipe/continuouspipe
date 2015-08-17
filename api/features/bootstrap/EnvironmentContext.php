<?php

use Behat\Behat\Context\Context;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ContinuousPipe\Pipe\Tests\FakeEnvironmentClient;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class EnvironmentContext implements Context
{
    /**
     * @var ProviderContext
     */
    private $providerContext;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var FakeEnvironmentClient
     */
    private $fakeEnvironmentClient;

    /**
     * @param Kernel $kernel
     * @param FakeEnvironmentClient $fakeEnvironmentClient
     */
    public function __construct(Kernel $kernel, FakeEnvironmentClient $fakeEnvironmentClient)
    {
        $this->kernel = $kernel;
        $this->fakeEnvironmentClient = $fakeEnvironmentClient;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->providerContext = $scope->getEnvironment()->getContext('ProviderContext');
    }

    /**
     * @When I send a valid deployment request
     */
    public function iSendAValidDeploymentRequest()
    {
        $this->providerContext->iHaveAFakeProviderNamed('foo');
        $this->sendDeploymentRequest('fake/foo', 'foo');
    }

    /**
     * @Then the environment should be created or updated
     */
    public function theEnvironmentShouldBeCreatedOrUpdated()
    {
        $createdOrUpdatedEnvironments = $this->fakeEnvironmentClient->getCreatedOrUpdated();

        if (0 === count($createdOrUpdatedEnvironments)) {
            throw new \RuntimeException('Expected to have at least one created or updated environment, found 0');
        }
    }

    /**
     * @param string $providerName
     * @param string $environmentName
     */
    public function sendDeploymentRequest($providerName, $environmentName)
    {
        $simpleAppComposeContents = file_get_contents(__DIR__.'/../fixtures/simple-app.yml');
        $contents = json_encode([
            'environmentName' => $environmentName,
            'providerName' => $providerName,
            'dockerComposeContents' => $simpleAppComposeContents,
        ]);

        $this->response = $this->kernel->handle(Request::create('/deployments', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], $contents));

        if (200 !== $this->response->getStatusCode()) {
            echo $this->response->getContent();

            throw new \RuntimeException(sprintf('Expected response code 200, got %d', $this->response->getStatusCode()));
        }
    }
}
