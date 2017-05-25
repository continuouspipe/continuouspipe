<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
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
     * @When I ask the external relations
     */
    public function iAskTheExternalRelations()
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/tides/%s/external-relations', $this->tideContext->getCurrentTideUuid()),
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
     * @Then I should see the GitHub pull-request #:number
     */
    public function iShouldSeeTheGithubPullRequest($number)
    {
        $relations = \GuzzleHttp\json_decode($this->response->getContent(), true);
        $matchingRelations = array_filter($relations, function(array $relation) use ($number) {
            return $relation['type'] == 'github' && substr($relation['link'], -strlen($number)-1) == '/'.$number;
        });

        if (count($matchingRelations) == 0) {
            throw new \RuntimeException('Relation not found');
        }
    }

    /**
     * @Then I should see no external relation
     */
    public function iShouldSeeNoExternalRelation()
    {
        $relations = \GuzzleHttp\json_decode($this->response->getContent(), true);

        if (count($relations) != 0) {
            throw new \RuntimeException(sprintf(
                'Expected 0 relations, found %d',
                count($relations)
            ));
        }
    }

    /**
     * @Then I should see that the tide is failed
     */
    public function iShouldSeeThatTheTideIsFailed()
    {
        $decoded = $this->getJson();

        if ($decoded['status'] != Tide::STATUS_FAILURE) {
            throw new \RuntimeException(sprintf(
                'Expected to see the tide status as failed, got "%s"',
                $decoded['status']
            ));
        }
    }

    /**
     * @Then I should see in the list the following deployed services:
     */
    public function iShouldSeeInTheListTheFollowingDeployedServices(TableNode $table)
    {
        $decoded = $this->getJson();
        if (!array_key_exists('deployed_services', $decoded)) {
            throw new \RuntimeException('Expected the JSON to contain a "services" key but not found');
        }

        $services = $decoded['deployed_services'];
        if (empty($services)) {
            throw new \RuntimeException('No services found in the answer');
        }

        foreach ($table->getHash() as $expectedService) {
            if (!array_key_exists($expectedService['name'], $services)) {
                throw new \RuntimeException(sprintf(
                    'Service "%s" is not found',
                    $expectedService['name']
                ));
            }

            $address = $services[$expectedService['name']]['public_endpoint']['address'];
            if ($expectedService['address'] != $address) {
                throw new \RuntimeException(sprintf(
                    'Address "%s" not found in service %s',
                    $expectedService['address'],
                    $expectedService['name']
                ));
            }
        }
    }

    /**
     * @Then I should see that the tide is running
     */
    public function iShouldSeeThatTheTideIsRunning()
    {
        $foundStatus = $this->getJson()['status'];
        if ($foundStatus != 'running') {
            throw new \RuntimeException(sprintf(
                'Found status "%s" but expected "running"',
                $foundStatus
            ));
        }
    }

    /**
     * @Then I should see that the current task is the build task
     */
    public function iShouldSeeThatTheCurrentTaskIsTheBuildTask()
    {
        $this->assetCurrentTaskIs('build');
    }

    /**
     * @Then I should see that the current task is the deploy task
     */
    public function iShouldSeeThatTheCurrentTaskIsTheDeployTask()
    {
        $this->assetCurrentTaskIs('deploy');
    }

    /**
     * @Then I should see the :environment environment
     */
    public function iShouldSeeTheEnvironment($environment)
    {
        $decoded = $this->getJson();
        if (!array_key_exists('environment', $decoded)) {
            throw new \RuntimeException('Expected the JSON to contain an "environment" but not found');
        }

        if ($decoded['environment']['name'] != $environment) {
            throw new \RuntimeException(sprintf('Expected the environment to be %s but it was %s', $environment, $decoded['environment']));
        }
    }

    /**
     * @param string $name
     */
    private function assetCurrentTaskIs($name)
    {
        $json = $this->getJson();
        if (!array_key_exists('current_task', $json)) {
            throw new \RuntimeException('Expected to find the `current_task` key but not found');
        }

        $currentTask = $json['current_task'];
        if ($currentTask['name'] != $name) {
            throw new \RuntimeException(sprintf(
                'Found "%s" as current task name, expected "%s"',
                $currentTask['name'],
                $name
            ));
        }

        if (empty($currentTask['log'])) {
            throw new \RuntimeException('Empty log line for the current task');
        }
    }

    /**
     * @return array
     */
    private function getJson()
    {
        return json_decode($this->response->getContent(), true);
    }
}
