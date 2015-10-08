<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;

class UserApiContext implements Context
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @When I create a new docker registry with the following configuration:
     */
    public function iCreateANewDockerRegistryWithTheFollowingConfiguration(TableNode $table)
    {
        $content = json_encode($table->getHash()[0]);

        $this->response = $this->kernel->handle(Request::create(
            '/api/v1/docker-registries',
            'POST', [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            $content
        ));
    }

    /**
     * @Then the new credentials should have been saved successfully
     */
    public function theNewCredentialsShouldHaveBeenSavedSuccessfully()
    {
        if ($this->response->getStatusCode() != 201) {
            echo $this->response->getContent();

            throw new \RuntimeException(sprintf(
                'Expected to have response code 201, but got %d',
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @Then I should receive a bad request error
     */
    public function iShouldReceiveABadRequestError()
    {
        if ($this->response->getStatusCode() != 400) {
            throw new \RuntimeException(sprintf(
                'Expected to have response code 400, but got %d',
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @Given I have the following docker registry credentials:
     */
    public function iHaveTheFollowingDockerRegistryCredentials(TableNode $table)
    {
    }

    /**
     * @When I ask the list of my docker registry credentials
     */
    public function iAskTheListOfMyDockerRegistryCredentials()
    {
        $this->response = $this->kernel->handle(Request::create(
            '/api/v1/docker-registries',
            'GET'
        ));
    }

    /**
     * @Then I should receive a list
     */
    public function iShouldReceiveAList()
    {
        if ($this->response->getStatusCode() != 200) {
            throw new \RuntimeException(sprintf(
                'Expected to have response code 200, but got %d',
                $this->response->getStatusCode()
            ));
        }

        $decoded = json_decode($this->response->getContent(), true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Expected to get an array in the JSON response');
        }
    }

    /**
     * @Then the list should contain the credential for server :serverAddress
     */
    public function theListShouldContainTheCredentialForServer($serverAddress)
    {
        $decoded = json_decode($this->response->getContent(), true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Expected to get an array in the JSON response');
        }

        $matchingCredentials = array_filter($decoded, function(array $row) use ($serverAddress) {
            return $row['serverAddress'] == $serverAddress;
        });

        if (0 == count($matchingCredentials)) {
            throw new \RuntimeException('No matching credentials found');
        }
    }
}