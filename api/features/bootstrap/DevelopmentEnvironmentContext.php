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
        $this->request(Request::create(
            '/flows/'.$flowUuid.'/development-environments/'.$uuid.'/initialization-token',
            'POST',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json'
            ],
            $string->getRaw()
        ));

        $this->assertResponseCode(201);
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
