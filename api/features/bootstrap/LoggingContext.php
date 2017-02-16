<?php

use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class LoggingContext implements Context
{

    /**
     * @var DebugLoggerInterface
     */
    private $logger;

    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(KernelInterface $kernel, DebugLoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->kernel = $kernel;
    }

    /**
     * @When a user opens a non-existent page
     */
    public function aUserOpensANonExistentPage()
    {
        try {
            $this->kernel->handle(Request::create('/no-such-page', 'GET'));
        } catch (NotFoundHttpException $e) {
        }
    }

    /**
     * @Then I should see a not found exception in the logs with :logLevel level
     */
    public function iShouldSeeANotFoundExceptionInTheLogsWithLevel($logLevel)
    {
        $matchingLogEntries = array_filter($this->logger->getLogs(), function(array $log) use($logLevel) {
            return strtoupper($logLevel) === $log['priorityName']
                && isset($log['context']['exception'])
                && $log['context']['exception'] instanceof NotFoundHttpException;
        });

        if (0 === count($matchingLogEntries)) {
            throw new \UnexpectedValueException('No matching log entry found.');
        }
    }

    /**
     * @Given the number of not found exceptions in the log should be :count
     */
    public function theNumberOfNotFoundExceptionsShouldBe($count)
    {
        $matchingLogEntries = array_filter($this->logger->getLogs(), function(array $log) {
            return isset($log['context']['exception'])
                && $log['context']['exception'] instanceof NotFoundHttpException;
        });

        if ($count != count($matchingLogEntries)) {
            throw new \UnexpectedValueException(
                sprintf('Expected to have %d messages, but found %d.', $count, count($matchingLogEntries))
            );
        }
    }
}