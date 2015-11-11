<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Builder\Docker\Client;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use LogStream\EmptyLogger;
use LogStream\LoggerFactory;
use LogStream\Tests\InMemory\InMemoryLogger;

class DockerContext implements Context
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var \Exception|null
     */
    private $exception;
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param Client $client
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(Client $client, LoggerFactory $loggerFactory)
    {
        $this->client = $client;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @When a built image is pushed
     */
    public function aBuiltImageIsPushed()
    {
        try {
            $this->client->push(new Image('name', 'tag'), new RegistryCredentials(), $this->loggerFactory->create());
        } catch (\Exception $e) {
            $this->exception = $e;
        }
    }

    /**
     * @Then the push should be successful
     */
    public function thePushShouldBeSuccessful()
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }
    }
}
