<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ContinuousPipe\Adapter\ProviderRepository;
use ContinuousPipe\Pipe\Tests\FakeProvider;

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
     * @var ProviderRepository
     */
    private $providerRepository;

    /**
     * @param Kernel $kernel
     * @param ProviderRepository $providerRepository
     */
    public function __construct(Kernel $kernel, ProviderRepository $providerRepository)
    {
        $this->kernel = $kernel;
        $this->providerRepository = $providerRepository;
    }

    /**
     * @When I send a GET request to :path
     */
    public function iSendAGetRequestTo($path)
    {
        $this->response = $this->kernel->handle(Request::create($path, 'GET'));
    }

    /**
     * @Given I have a provider named :name
     */
    public function iHaveAFakeProviderNamed($name)
    {
        $this->providerRepository->create(new FakeProvider($name));
    }

    /**
     * @Then I should see this provider :name in the list of registered providers
     */
    public function iShouldSeeThisFakeProviderInTheListOfRegisteredProviders($name)
    {
        $this->iSendAGetRequestTo('/providers');

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
     * @When I test the provider :name
     */
    public function iTestThisProvider($name)
    {
        $this->response = $this->kernel->handle(Request::create(
            sprintf('/providers/%s/test', urlencode('fake/'.$name)),
            'POST'
        ));
    }

    /**
     * @Then I should see that the provider is valid
     */
    public function iShouldSeeThatTheProviderIsValid()
    {
        if ($this->response->getStatusCode() != 200) {
            throw new \LogicException(sprintf(
                'Expected response code to be 200, got %d',
                $this->response->getStatusCode()
            ));
        }
    }
}
