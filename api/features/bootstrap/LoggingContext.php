<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use LogStream\Log;
use LogStream\Tests\InMemory\InMemoryLogStore;
use LogStream\Tests\InMemoryLogClient;

class LoggingContext implements Context
{
    /**
     * @var InMemoryLogClient
     */
    private $inMemoryLogClient;

    /**
     * @param InMemoryLogClient $inMemoryLogClient
     */
    public function __construct(InMemoryLogClient $inMemoryLogClient)
    {
        $this->inMemoryLogClient = $inMemoryLogClient;
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
