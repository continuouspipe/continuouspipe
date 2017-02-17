<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use LogStream\Log;
use LogStream\Tests\InMemory\InMemoryLogStore;
use LogStream\Tests\InMemoryLogClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class LoggingContext implements Context
{
    /**
     * @var InMemoryLogClient
     */
    private $inMemoryLogClient;

    /**
     * @var KernelInterface
     */
    private $kernel;

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
    public function theNumberOfNotFoundExceptionsInTheLogShouldBe($count)
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
}
