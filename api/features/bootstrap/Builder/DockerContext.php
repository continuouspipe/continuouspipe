<?php

namespace Builder;

use Behat\Behat\Context\Context;
use ContinuousPipe\Builder\Docker\DockerFacade;
use ContinuousPipe\Builder\Docker\PushContext;
use ContinuousPipe\Builder\Engine;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use LogStream\EmptyLogger;
use LogStream\LoggerFactory;
use LogStream\Tests\InMemory\InMemoryLogger;

class DockerContext implements Context
{
    /**
     * @var DockerFacade
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
     * @param DockerFacade $client
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(DockerFacade $client, LoggerFactory $loggerFactory)
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
            $this->client->push(
                new PushContext(
                    '',
                    RegistryCredentials::fromAuthenticationString(''),
                    new Engine('docker')
                ),
                new Image('name', 'tag')
            );
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
