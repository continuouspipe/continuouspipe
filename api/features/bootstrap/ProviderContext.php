<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ContinuousPipe\Pipe\AdapterProviderRepository;
use ContinuousPipe\Pipe\Tests\FakeProvider;
use ContinuousPipe\Adapter\ProviderNotFound;

class ProviderContext implements Context
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var AdapterProviderRepository
     */
    private $providerRepository;

    /**
     * @param Kernel $kernel
     * @param AdapterProviderRepository $providerRepository
     */
    public function __construct(Kernel $kernel, AdapterProviderRepository $providerRepository)
    {
        $this->kernel = $kernel;
        $this->providerRepository = $providerRepository;
    }

    /**
     * @Given I have a provider named :name
     */
    public function iHaveAFakeProviderNamed($name)
    {
        $this->providerRepository->create(new FakeProvider($name));
    }

    /**
     * @When I request the list of providers
     */
    public function iRequestTheListOfProviders()
    {
        $this->response = $this->kernel->handle(Request::create('/providers', 'GET'));
    }

    /**
     * @Then I should see this provider :name in the list of registered providers
     */
    public function iShouldSeeThisFakeProviderInTheListOfRegisteredProviders($name)
    {
        if (200 !== $this->response->getStatusCode()) {
            echo $this->response->getContent();
            throw new \RuntimeException(sprintf(
                'Expected status 200, got %d',
                $this->response->getStatusCode()
            ));
        }

        $contents = $this->response->getContent();
        $json = json_decode($contents, true);

        if (!is_array($json)) {
            throw new \RuntimeException('Expected a JSON body');
        }

        $matchingProvider = array_filter($json, function(array $raw) use ($name) {
            return $raw['identifier'] == $name;
        });

        if (count($matchingProvider) === 0) {
            throw new \RuntimeException('No matching provider found');
        }
    }

    /**
     * @When I send a provider creation request for type :type with body:
     */
    public function iSendAProviderCreationRequestForTypeWithBody($type, PyStringNode $string)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/providers/%s', $type),
            'POST',
            [], [], [],
            [
                'CONTENT_TYPE' => 'application/json'
            ],
            $string->getRaw()
        ));
    }

    /**
     * @When I request the environment list of provider :name
     */
    public function iRequestTheEnvironmentListOfProvider($name)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/providers/fake/%s/environments', $name)
        ));
    }

    /**
     * @Then I should successfully receive the environment list
     */
    public function iShouldSuccessfullyReceiveTheEnvironmentList()
    {
        if ($this->response->getStatusCode() != 200) {
            throw new \LogicException(sprintf(
                'Expected response code to be 200, got %d',
                $this->response->getStatusCode()
            ));
        }
    }

    /**
     * @When I delete the provider named :name
     */
    public function iDeleteTheProviderNamed($name)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/providers/fake/%s', $name),
            'DELETE'
        ));
    }

    /**
     * @Then the provider :name should not exists
     */
    public function theProviderShouldNotExists($name)
    {
        try {
            $this->providerRepository->findByTypeAndIdentifier('fake', $name);

            throw new \RuntimeException(sprintf('Provider "%s" already found in repository', $name));
        } catch (ProviderNotFound $e) {}
    }
}
