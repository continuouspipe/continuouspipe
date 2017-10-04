<?php

namespace Builder;

use Behat\Behat\Context\Context;
use ContinuousPipe\Builder\Docker\Exception\DaemonException;
use ContinuousPipe\Builder\Docker\Exception\DaemonNetworkException;
use ContinuousPipe\Builder\Docker\Exception\PushAlreadyInProgress;
use ContinuousPipe\Builder\Docker\HttpClient\OutputHandler;
use ContinuousPipe\Builder\Docker\HttpClient\RawOutputHandler;

class DockerOutputContext implements Context
{
    /**
     * @var OutputHandler
     */
    private $outputHandler;

    /**
     * @var \Exception|null
     */
    private $exception;

    /**
     * @param OutputHandler $outputHandler
     */
    public function __construct(OutputHandler $outputHandler)
    {
        $this->outputHandler = $outputHandler;
    }

    /**
     * @When the Docker daemon returns the error :error
     */
    public function theDockerDaemonReturnsTheError($error)
    {
        try {
            $this->outputHandler->handle(['error' => $error]);
        } catch (\Exception $e) {
            $this->exception = $e;
        }
    }

    /**
     * @Then the identified error should be a daemon network error
     */
    public function theIdentifiedErrorShouldBeADaemonNetworkError()
    {
        $this->assertExceptionIs(DaemonNetworkException::class);
    }

    /**
     * @Then the identified error should be a daemon error
     */
    public function theIdentifiedErrorShouldBeADaemonError()
    {
        $this->assertExceptionIs(DaemonException::class);
    }

    /**
     * @Then the identified error should be a push already in progress error
     */
    public function theIdentifiedErrorShouldBeAPushAlreadyInProgressError()
    {
        $this->assertExceptionIs(PushAlreadyInProgress::class);
    }

    private function assertExceptionIs($className)
    {
        if (null === $this->exception) {
            throw new \RuntimeException('No exception found');
        }

        if (get_class($this->exception) != $className && !is_subclass_of($this->exception, $className)) {
            throw new \RuntimeException(sprintf(
                'Found %s instead',
                get_class($this->exception)
            ));
        }
    }
}
