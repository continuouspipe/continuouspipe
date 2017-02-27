<?php

use Behat\Behat\Context\Context;
use Helpers\KernelClientHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class DevelopmentEnvironmentContext implements Context
{
    use KernelClientHelper;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @When I create a development environment client named :name for the flow :flowUuid
     */
    public function iCreateADevelopmentEnvironmentClientNamedForTheFlow($name, $flowUuid)
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
}
