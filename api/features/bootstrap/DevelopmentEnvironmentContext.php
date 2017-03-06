<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\DevelopmentEnvironment\Aggregate\Events\DevelopmentEnvironmentCreated;
use ContinuousPipe\DevelopmentEnvironment\Aggregate\FromEvents\EventStream;
use ContinuousPipe\Events\EventStore\EventStore;
use Helpers\KernelClientHelper;
use Ramsey\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class DevelopmentEnvironmentContext implements Context
{
    use KernelClientHelper;

    /**
     * @var EventStore
     */
    private $eventStore;
    /**
     * @var MessageBus
     */
    private $eventBus;

    public function __construct(KernelInterface $kernel, MessageBus $eventBus)
    {
        $this->kernel = $kernel;
        $this->eventBus = $eventBus;
    }

    /**
     * @Given the user :username have a development environment :uuid for the flow :flowUuid
     */
    public function theUserHaveADevelopmentEnvironmentForTheFlow($username, $uuid, $flowUuid)
    {
        $environmentUuid = Uuid::fromString($uuid);

        $this->eventBus->handle(new DevelopmentEnvironmentCreated(
            $environmentUuid,
            Uuid::fromString($flowUuid),
            $username,
            'Name of env #'.$uuid,
            new \DateTime()
        ));
    }

    /**
     * @Given an initialization token have been created for the development environment :uuid of the flow :flowUuid and the branch :branch
     */
    public function anInitializationTokenHaveBeenCreatedForTheDevelopmentEnvironmentAndTheBranch($uuid, $flowUuid, $branch)
    {
        $this->iCreateAnInitializationTokenForTheDevelopmentEnvironmentOfTheFlow(
            $uuid,
            $flowUuid,
            json_encode(['git_branch' => $branch])
        );
    }

    /**
     * @When I create a development environment named :name for the flow :flowUuid
     */
    public function iCreateADevelopmentEnvironmentNamedForTheFlow($name, $flowUuid)
    {
        $this->request(Request::create(
            '/flows/'.$flowUuid.'/development-environments',
            'POST',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode([
                'name' => $name,
            ])
        ));

        $this->assertResponseCode(201);
    }

    /**
     * @When I request the list of the development environments of the flow :flowUuid
     */
    public function iListTheDevelopmentEnvironmentsOfTheFlow($flowUuid)
    {
        $this->request(Request::create(
            '/flows/'.$flowUuid.'/development-environments',
            'GET'
        ));
    }

    /**
     * @Given I create an initialization token for the development environment :uuid of the flow :flowUuid with the following parameters:
     */
    public function iCreateAnInitializationTokenForTheDevelopmentEnvironmentOfTheFlowWithTheFollowingParameters($uuid, $flowUuid, PyStringNode $string)
    {
        $this->iCreateAnInitializationTokenForTheDevelopmentEnvironmentOfTheFlow($uuid, $flowUuid, $string->getRaw());
    }

    private function iCreateAnInitializationTokenForTheDevelopmentEnvironmentOfTheFlow($uuid, $flowUuid, string $body)
    {
        $this->request(Request::create(
            '/flows/'.$flowUuid.'/development-environments/'.$uuid.'/initialization-token',
            'POST',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json'
            ],
            $body
        ));

        $this->assertResponseCode(201);
    }

    /**
     * @When I request the status of the development environment :uuid of the flow :flowUuid
     */
    public function iRequestTheStatusOfTheDevelopmentEnvironmentOfTheFlow($uuid, $flowUuid)
    {
        $this->request(Request::create(
            '/flows/'.$flowUuid.'/development-environments/'.$uuid.'/status',
            'GET'
        ));

        $this->assertResponseCode(200);
    }

    /**
     * @Then I should see that the status of the development environment is :status
     */
    public function iShouldSeeThatTheStatusOfTheDevelopmentEnvironmentIs($status)
    {
        $foundStatus = $this->jsonResponse()['status'];

        if ($foundStatus != $status) {
            throw new \RuntimeException(sprintf(
                'Expected status "%s" but found "%s"',
                $status,
                $foundStatus
            ));
        }
    }

    /**
     * @Then I should see the last tide of my development environment
     */
    public function iShouldSeeTheLastTideOfMyDevelopmentEnvironment()
    {
        var_dump($this->jsonResponse());
        if (!isset($this->jsonResponse()['last_tide'])) {
            throw new \RuntimeException('The last tide was not found');
        }
    }

    /**
     * @Then I should see that the cluster identifier of the development environment is :clusterIdentifier
     */
    public function iShouldSeeThatTheClusterIdentifierOfTheDevelopmentEnvironmentIs($clusterIdentifier)
    {
        $foundClusterIdentifier = $this->jsonResponse()['cluster_identifier'];

        if ($foundClusterIdentifier != $clusterIdentifier) {
            throw new \RuntimeException(sprintf(
                'Expected cluster identifier "%s" but found "%s"',
                $clusterIdentifier,
                $foundClusterIdentifier
            ));
        }
    }

    /**
     * @Then I should see that the public endpoint of the service :service of my development environment is :address
     */
    public function iShouldSeeThatThePublicEndpointOfTheServiceOfMyDevelopmentEnvironmentIs($service, $address)
    {
        $foundEndpoints = $this->jsonResponse()['public_endpoints'];
        foreach ($foundEndpoints as $endpoint) {
            if ($endpoint['name'] == $service && $endpoint['address'] == $address) {
                return;
            }
        }

        throw new \RuntimeException('The service endpoint was not found');
    }

    /**
     * @Then I should see the environment name of my development environment
     */
    public function iShouldSeeTheEnvironmentNameOfMyDevelopmentEnvironment()
    {
        if (!isset($this->jsonResponse()['environment_name'])) {
            throw new \RuntimeException('Environment name not found');
        }
    }

    /**
     * @Then I should see the development environment :name
     */
    public function iShouldSeeTheDevelopmentEnvironment($name)
    {
        $this->assertResponseCode(200);

        foreach ($this->jsonResponse() as $row) {
            if ($row['name'] == $name) {
                return;
            }
        }

        throw new \RuntimeException('No development environment with that name found');
    }

    /**
     * @Then I receive a token that contains the following base64 decoded and comma separated values:
     */
    public function iReceiveATokenThatContainsTheFollowingBaseDecodedAndCommaSeparatedValues(TableNode $table)
    {
        $row = $table->getRow(0);
        $expectedToken = base64_encode(implode(',', $row));
        $token = $this->jsonResponse()['token'];

        if ($expectedToken != $token) {
            throw new \RuntimeException(sprintf(
                'Found token "%s" instead',
                $token
            ));
        }
    }
}
