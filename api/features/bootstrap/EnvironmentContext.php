<?php

use Behat\Behat\Context\Context;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\Pipe\View\Deployment;
use ContinuousPipe\Pipe\EventBus\EventStore;
use Rhumsaa\Uuid\Uuid;

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
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var Uuid
     */
    private $lastDeploymentUuid;

    /**
     * @param Kernel     $kernel
     * @param EventStore $eventStore
     */
    public function __construct(Kernel $kernel, EventStore $eventStore)
    {
        $this->kernel = $kernel;
        $this->eventStore = $eventStore;
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
        $events = $this->eventStore->findByDeploymentUuid(
            $this->lastDeploymentUuid
        );

        if (0 === count($events)) {
            throw new \RuntimeException('Expected to have at least one event for this deployment, found 0');
        }
    }

    /**
     * @param string $providerName
     * @param string $environmentName
     * @param string $template
     */
    public function sendDeploymentRequest($providerName, $environmentName, $template = 'simple-app')
    {
        $simpleAppComposeContents = file_get_contents(__DIR__.'/../fixtures/'.$template.'.yml');
        $contents = json_encode([
            'environmentName' => $environmentName,
            'providerName' => $providerName,
            'dockerComposeContents' => $simpleAppComposeContents,
        ]);

        $this->response = $this->kernel->handle(Request::create('/deployments', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $contents));

        if (200 !== $this->response->getStatusCode()) {
            echo $this->response->getContent();

            throw new \RuntimeException(sprintf('Expected response code 200, got %d', $this->response->getStatusCode()));
        }

        $deployment = json_decode($this->response->getContent(), true);
        if (Deployment::STATUS_SUCCESS != $deployment['status']) {
            throw new \RuntimeException(sprintf(
                'Expected deployment status to be "%s" but got "%s"',
                Deployment::STATUS_SUCCESS,
                $deployment['status']
            ));
        }

        $this->lastDeploymentUuid = Uuid::fromString($deployment['uuid']);
    }
}
