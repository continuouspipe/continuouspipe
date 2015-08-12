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
        $matchingLogs = $this->findLogsByContents($contents, $this->logStore->findAll());

        if (count($matchingLogs) === 0) {
            throw new \RuntimeException('No matching log found');
        }
    }

    /**
     * @Then a :contents log should be created under the :parentContents one
     */
    public function aBuildingImageLogShouldBeCreatedUnderTheOne($contents, $parentContents)
    {
        $this->aLogShouldBeCreated($parentContents);

        $matchingParentLogs = $this->findLogsByContents($parentContents, $this->logStore->findAll());
        $parentLog = current($matchingParentLogs);

        $matchingChildren = $this->findLogsByContents($contents, $this->logStore->findAllByParent($parentLog));
        if (count($matchingChildren) == 0) {
            throw new \RuntimeException('No matching log in parent\'s children');
        }
    }

    /**
     * @Then the :contents log should be successful
     */
    public function theLogShouldBeSuccessful($contents)
    {
        $logs = $this->findLogsByContents($contents, $this->logStore->findAll());

        foreach ($logs as $log) {
            if ($log->getStatus() != 'success') {
                throw new \RuntimeException(sprintf(
                    'Got status "%s" but expected "success"',
                    $log->getStatus()
                ));
            }
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
}
