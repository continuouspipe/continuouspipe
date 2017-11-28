<?php

namespace Authenticator;

use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
        $this->assertLogLevel(NotFoundHttpException::class, $logLevel);
    }

    /**
     * @Given the number of not found exceptions in the log should be :count
     */
    public function theNumberOfNotFoundExceptionsShouldBe($count)
    {
        $this->assertLogEntriesCount(NotFoundHttpException::class, $count);
    }

    /**
     * @When I try to access an URL that I am not allowed to open
     */
    public function iTryToAccessAnURLThatIAmNotAllowedToOpen()
    {
        try {
            $this->kernel->handle(Request::create('/test/access-denied-page', 'GET'));
        } catch (AccessDeniedHttpException $e) {
        }
    }

    /**
     * @Then I should see an access denied exception in the logs with :logLevel level
     */
    public function iShouldSeeAnAccessDeniedExceptionInTheLogsWithLevel($logLevel)
    {
        $this->assertLogLevel(AccessDeniedHttpException::class, $logLevel);
    }

    /**
     * @Given the number of access denied exceptions in the log should be :count
     */
    public function theNumberOfAccessDeniedExceptionsInTheLogShouldBe($count)
    {
        $this->assertLogEntriesCount(AccessDeniedHttpException::class, $count);
    }

    private function assertLogLevel(string $exceptionClass, string $logLevel)
    {
        $matchingLogEntries = array_filter(
            $this->logger->getLogs(), function (array $log) use ($exceptionClass, $logLevel) {
            return strtoupper($logLevel) === $log['priorityName']
                && isset($log['context']['exception'])
                && $log['context']['exception'] instanceof $exceptionClass;
        }
        );

        if (0 === count($matchingLogEntries)) {
            throw new \UnexpectedValueException('No matching log entry found.');
        }
    }

    private function assertLogEntriesCount(string $exceptionClass, int $count)
    {
        $matchingLogEntries = array_filter(
            $this->logger->getLogs(), function (array $log) use($exceptionClass) {
            return isset($log['context']['exception'])
                && $log['context']['exception'] instanceof $exceptionClass;
        }
        );

        if ($count != count($matchingLogEntries)) {
            throw new \UnexpectedValueException(
                sprintf('Expected to have %d messages, but found %d.', $count, count($matchingLogEntries))
            );
        }
    }
}