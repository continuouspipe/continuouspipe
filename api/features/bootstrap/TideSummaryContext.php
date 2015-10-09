<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\River\View\Tide;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class TideSummaryContext implements Context
{
    /**
     * @var \TideContext
     */
    private $tideContext;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->tideContext = $scope->getEnvironment()->getContext('TideContext');
    }

    /**
     * @When I ask the summary of the tide
     */
    public function iAskTheSummaryOfTheTide()
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/tides/%s/summary', $this->tideContext->getCurrentTideUuid()),
            'GET'
        ));

        if ($this->response->getStatusCode() != 200) {
            throw new \RuntimeException(sprintf(
                'Expected to get the status code 200, got %d',
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @Then I should see that the tide is failed
     */
    public function iShouldSeeThatTheTideIsFailed()
    {
        $decoded = json_decode($this->response->getContent(), true);

        if ($decoded['status'] != Tide::STATUS_FAILURE) {
            throw new \RuntimeException(sprintf(
                'Expected to see the tide status as failed, got "%s"',
                $decoded['status']
            ));
        }
    }

    /**
     * @Then I should see the list of the deployed services and their addresses
     */
    public function iShouldSeeTheListOfTheDeployedServicesAndTheirAddresses()
    {
        $decoded = json_decode($this->response->getContent(), true);
        if (!array_key_exists('services', $decoded)) {
            throw new \RuntimeException('Expected the JSON to contain a "services" key but not found');
        }

        if (empty($decoded['services'])) {
            throw new \RuntimeException('No services found in the answer');
        }

        if (!array_key_exists('public_endpoints', $decoded['services'][0])) {
            throw new \RuntimeException('No public endpoint found for first service');
        }
    }
}
