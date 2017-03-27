<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Helpers\KernelClientHelper;
use LogStream\Log;
use LogStream\Tests\InMemory\InMemoryLogStore;
use LogStream\Tests\InMemoryLogClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class LoggingContext implements Context
{
    use KernelClientHelper;

    /**
     * @var InMemoryLogClient
     */
    private $inMemoryLogClient;

    /**
     * @var DebugLoggerInterface
     */
    private $logger;

    /**
     * @param KernelInterface $kernel
     * @param InMemoryLogClient $inMemoryLogClient
     * @param DebugLoggerInterface $logger
     */
    public function __construct(
        KernelInterface $kernel,
        InMemoryLogClient $inMemoryLogClient,
        DebugLoggerInterface $logger
    ) {
        $this->inMemoryLogClient = $inMemoryLogClient;
        $this->kernel = $kernel;
        $this->logger = $logger;
    }

    /**
     * @Then a :contents log should be created
     * @Then the :contents log should be created
     */
    public function aLogShouldBeCreated($contents)
    {
        $this->findLogByContents($contents, $this->findAllLogs());
    }

    /**
     * @Then a :contetns log should be created only once
     */
    public function aLogShouldBeCreatedOnlyOnce($contents)
    {
        $matchingLogs = $this->findLogsByContents($contents, $this->findAllLogs());

        if (count($matchingLogs) != 1) {
            throw new \RuntimeException(sprintf(
                'Found this log %d times',
                count($matchingLogs)
            ));
        }
    }



    /**
     * @Then a log of type :type should be created under the log :parentContents
     */
    public function aLogOfTypeShouldBeCreatedUnderTheLog($type, $parentContents)
    {
        $this->aLogShouldBeCreated($parentContents);

        $parentLog = $this->findLogByContents($parentContents, $this->findAllLogs());
        $children = $this->findAllLogsByParent($parentLog);
        $this->findLogByType($type, $children);
    }

    /**
     * @Then a log of type :type should contain the following attributes:
     */
    public function aLogOfTypeShouldContainTheFollowingAttributes($type, TableNode $table)
    {
        $log = $this->findLogByType($type, $this->findAllLogs());
        $expectedAttributes = $table->getHash()[0];

        foreach ($expectedAttributes as $key => $value) {
            if (!array_key_exists($key, $log)) {
                throw new \RuntimeException(sprintf('Attribute "%s" not found', $key));
            }

            if ($log[$key] != $value) {
                throw new \RuntimeException(sprintf('Found unexpected value "%s" for the attribute "%s"', $log[$key], $key));
            }
        }
    }

    /**
     * @Then a :contents log should be created under the :parentContents one
     */
    public function aBuildingImageLogShouldBeCreatedUnderTheOne($contents, $parentContents)
    {
        $this->aLogShouldBeCreated($parentContents);

        $parentLog = $this->findLogByContents($parentContents, $this->findAllLogs());
        $children = $this->findAllLogsByParent($parentLog);
        $this->findLogByContents($contents, $children);
    }

    /**
     * @Then the :contents log should be successful
     */
    public function theLogShouldBeSuccessful($contents)
    {
        $log = $this->findLogByContents($contents, $this->findAllLogs());
        if ($log['status'] != 'success') {
            throw new \RuntimeException(sprintf(
                'Got status "%s" but expected "success"',
                $log['status']
            ));
        }
    }

    /**
     * @Then the log of type :type should be successful
     */
    public function theLogOfTypeShouldBeSuccessful($type)
    {
        $log = $this->findLogByType($type, $this->findAllLogs());
        if ($log['status'] != 'success') {
            throw new \RuntimeException(sprintf(
                'Got status "%s" but expected "success"',
                $log['status']
            ));
        }
    }

    /**
     * @Then the :contents log should be failed
     */
    public function theLogShouldBeFailed($contents)
    {
        $log = $this->findLogByContents($contents, $this->findAllLogs());
        if ($log['status'] != 'failure') {
            throw new \RuntimeException(sprintf(
                'Got status "%s" but expected "faillure"',
                $log['status']
            ));
        }
    }

    /**
     * @Then a log containing :contents should be created
     */
    public function aLogContainingShouldBeCreated($contents)
    {
        $matchingLogs = array_values(array_filter($this->findAllLogs(), function (array $log) use ($contents) {
            return array_key_exists('contents', $log) && false !== strpos($log['contents'], $contents);
        }));

        if (0 === count($matchingLogs)) {
            throw new \RuntimeException('No matching logs found');
        }
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
     * @Then I should see an access denied exception in the logs with :logLevel level
     */
    public function iShouldSeeAnAccessDeniedExceptionInTheLogsWithLevel($logLevel)
    {
        $this->assertLogLevel(AccessDeniedHttpException::class, $logLevel);
    }

    /**
     * @Given the number of not found exceptions in the log should be :count
     */
    public function theNumberOfNotFoundExceptionsInTheLogShouldBe($count)
    {
        $this->assertLogEntriesCount(NotFoundHttpException::class, $count);
    }

    /**
     * @Given the number of access denied exceptions in the log should be :count
     */
    public function theNumberOfAccessDeniedExceptionsInTheLogShouldBe($count)
    {
        $this->assertLogEntriesCount(AccessDeniedHttpException::class, $count);
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
     * @Then I should see a runtime exception in the logs tagged with
     */
    public function iShouldSeeARuntimeExceptionInTheLogsTaggedWith(TableNode $table)
    {
        $tagNames = array_map(function($row) {
            return $row['Tag name'];
        }, $table->getColumnsHash());
        $tagValues = array_map(function($row) {
            return $row['Tag value'];
        }, $table->getColumnsHash());
        $tags = array_combine($tagNames, $tagValues);

        $position = $this->findPositionByExceptionType(\RuntimeException::class);
        $this->assertLogHasTagsAtPosition($position, $tags);
    }

    /**
     * @When I start an operation with the tide :uuid that fails
     */
    public function iStartAnOperationWithTheTideThatFails($uuid)
    {
        $this->request(Request::create("/test/tide/$uuid/operation-failed", 'GET'));

        $this->assertResponseCode(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @When a webhook is received from GitHub for the flow :uuid that fails
     */
    public function aWebhookIsReceivedFromGitHubForTheFlowThatFails($uuid)
    {
        $this->request(Request::create("/test/github/webhook/flow/$uuid/operation-failed", 'GET'));

        $this->assertResponseCode(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @When a webhook is received from BitBucket for the flow :uuid that fails
     */
    public function aWebhookIsReceivedFromBitBucketForTheFlowThatFails($uuid)
    {
        $this->request(Request::create("/test/bitbucket/webhook/flow/$uuid/operation-failed", 'GET'));

        $this->assertResponseCode(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @When a worker receives a tide command with UUID :uuid that fails
     */
    public function aWorkerReceivesAStartTideCommandWithUUIDThatFails($uuid)
    {
        $this->request(Request::create("/test/worker/tide-command/$uuid/operation-failed", 'GET'));

        $this->assertResponseCode(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @Given I should see the :message message in the log
     */
    public function iShouldSeeTheWarningMessageInTheLog($message)
    {
        foreach ($this->logger->getLogs() as $logEntry) {
            if ($message === $logEntry['message']) {
                return;
            }
        }

        throw new \RuntimeException('The specified message not found in the log.');
    }

    /**
     * @param string           $contents
     * @param array $logCollection
     *
     * @return array
     */
    private function findLogsByContents($contents, $logCollection)
    {
        return array_values(array_filter($logCollection, function (array $log) use ($contents) {
            return array_key_exists('contents', $log) && $log['contents'] == $contents;
        }));
    }

    /**
     * @param string $type
     * @param array $logCollection
     *
     * @return array
     */
    private function findLogsByType($type, $logCollection)
    {
        return array_values(array_filter($logCollection, function (array $log) use ($type) {
            return array_key_exists('type', $log) && $log['type'] == $type;
        }));
    }

    /**
     * @param string           $contents
     * @param array  $logCollection
     *
     * @return array
     */
    private function findLogByContents($contents, $logCollection)
    {
        $matchingLogs = $this->findLogsByContents($contents, $logCollection);
        if (count($matchingLogs) === 0) {
            throw new \RuntimeException('No matching log found');
        }

        return $matchingLogs[0];
    }

    private function findLogByType($type, $logCollection) : array
    {
        $matchingLogs = $this->findLogsByType($type, $logCollection);
        if (count($matchingLogs) === 0) {
            throw new \RuntimeException('No matching log found');
        }

        return $matchingLogs[0];
    }

    /**
     * @return array
     */
    private function findAllLogs()
    {
        return $this->inMemoryLogClient->getLogs();
    }

    /**
     * @param string $parent
     *
     * @return array
     */
    private function findAllLogsByParent(array $parent)
    {
        return array_values(array_filter($this->findAllLogs(), function(array $log) use ($parent) {
            return array_key_exists('parent', $log) && $log['parent'] == $parent['_id'];
        }));
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

    private function findPositionByExceptionType(string $exceptionClass)
    {
        foreach ($this->logger->getLogs() as $index => $log) {
            if (isset($log['context']['exception'])
                && get_class($log['context']['exception']) === $exceptionClass) {
                return $index;
            }
        }

        throw new \RuntimeException(sprintf('No log entries for exception type %s has found.', $exceptionClass));
    }

    private function assertLogHasTagsAtPosition(int $index, array $tags)
    {
        $logs = $this->logger->getLogs();

        if (!isset($logs[$index])) {
            throw new \OutOfBoundsException(sprintf('Log index %d out of bounds [0, %d]', $index, count($logs) - 1));
        }

        foreach ($tags as $name => $value) {
            if (!isset($logs[$index]['context']['tags'][$name])) {
                throw new \UnexpectedValueException(sprintf('No tag with name %s found.', $name));
            }
            if ($logs[$index]['context']['tags'][$name] != $value) {
                throw new \UnexpectedValueException(
                    sprintf(
                        'Tag %s has value "%s", but expected to get "%s".',
                        $name,
                        $logs[$index]['context']['tags'][$name], $value
                    )
                );
            }
        }
    }
}
