<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\River\Tests\Logging\InMemoryLogStore;

class LoggingContext implements Context
{
    /**
     * @var InMemoryLogStore
     */
    private $logStore;

    /**
     * @param InMemoryLogStore $logStore
     */
    public function __construct(InMemoryLogStore $logStore)
    {
        $this->logStore = $logStore;
    }

    /**
     * @Then a :contents log should be created
     */
    public function aLogShouldBeCreated($contents)
    {
        $this->findLogByContents($contents, $this->logStore->findAll());
    }

    /**
     * @Then a :contents log should be created under the :parentContents one
     */
    public function aBuildingImageLogShouldBeCreatedUnderTheOne($contents, $parentContents)
    {
        $this->aLogShouldBeCreated($parentContents);

        $parentLog = $this->findLogByContents($parentContents, $this->logStore->findAll());
        $this->findLogByContents($contents, $this->logStore->findAllByParent($parentLog));
    }

    /**
     * @Then the :contents log should be successful
     */
    public function theLogShouldBeSuccessful($contents)
    {
        $log = $this->findLogByContents($contents, $this->logStore->findAll());
        if ($log->getStatus() != 'success') {
            throw new \RuntimeException(sprintf(
                'Got status "%s" but expected "success"',
                $log->getStatus()
            ));
        }
    }

    /**
     * @Then the :contents log should be failed
     */
    public function theLogShouldBeFailed($contents)
    {
        $log = $this->findLogByContents($contents, $this->logStore->findAll());
        if ($log->getStatus() != 'failure') {
            throw new \RuntimeException(sprintf(
                'Got status "%s" but expected "faillure"',
                $log->getStatus()
            ));
        }
    }

    /**
     * @param string $contents
     * @param \LogStream\Log[] $logCollection
     * @return \LogStream\Log[]
     */
    private function findLogsByContents($contents, $logCollection)
    {
        return array_filter($logCollection, function(\LogStream\Log $log) use ($contents) {
            if ($log instanceof \LogStream\WrappedLog) {
                $log = $log->getNode();
            }

            return $log instanceof \LogStream\Node\Text && $log->getText() == $contents;
        });
    }

    /**
     * @param string $contents
     * @param \LogStream\Log[] $logCollection
     * @return \LogStream\Log
     */
    private function findLogByContents($contents, $logCollection)
    {
        $matchingLogs = $this->findLogsByContents($contents, $logCollection);
        if (count($matchingLogs) === 0) {
            throw new \RuntimeException('No matching log found');
        }

        return current($matchingLogs);
    }
}
