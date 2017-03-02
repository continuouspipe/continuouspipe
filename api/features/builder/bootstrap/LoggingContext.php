<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Builder\Reporting\TracedPublisher;
use LogStream\Log;
use LogStream\Tests\InMemoryLogClient;
use LogStream\TraceableClient;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class LoggingContext implements Context
{
    /**
     * @var TraceableClient
     */
    private $traceableClient;

    /**
     * @var InMemoryLogClient
     */
    private $inMemoryLogClient;
    /**
     * @var TracedPublisher
     */
    private $tracedPublisher;

    public function __construct(TraceableClient $traceableClient, InMemoryLogClient $inMemoryLogClient, TracedPublisher $tracedPublisher)
    {
        $this->traceableClient = $traceableClient;
        $this->inMemoryLogClient = $inMemoryLogClient;
        $this->tracedPublisher = $tracedPublisher;
    }

    /**
     * @Then a report should be published
     */
    public function aReportShouldBePublished()
    {
        $reports = $this->tracedPublisher->getPublishedReports();

        if (0 === count($reports)) {
            throw new \RuntimeException('No published reports found');
        }
    }

    /**
     * @Then the published report should contain :value for the key :key
     */
    public function thePublishedReportShouldContainForTheKey($value, $key)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($this->tracedPublisher->getPublishedReports() as $report) {
            $foundValue = $propertyAccessor->getValue($report, '['.$key.']');

            if ($foundValue == $value) {
                return;
            }
        }

        throw new \RuntimeException('Value not found');
    }

    /**
     * @Then a log containing :text should be created
     */
    public function aLogContainingShouldBeCreated($text)
    {
        return $this->findLogContaining($this->traceableClient->getCreated(), $text);
    }

    /**
     * @Then the log containing :text should be failed
     */
    public function theLogContainingShouldBeFailed($text)
    {
        $logs = array_map(function(string $identifier) {
            return $this->inMemoryLogClient->find($identifier);
        }, array_keys($this->inMemoryLogClient->getLogs()));

        $log = $this->findLogContaining($logs, $text);

        if ($log->getStatus() != Log::FAILURE) {
            throw new \RuntimeException(sprintf('The log containing the text is not failed, but %s', $log->getStatus()));
        }
    }

    /**
     * @param Log[] $logs
     * @param string $text
     *
     * @return Log
     */
    private function findLogContaining(array $logs, string $text): Log
    {
        foreach ($logs as $created) {
            $serialized = $created->getNode()->jsonSerialize();

            if ($serialized['type'] == 'text' && false !== strpos($serialized['contents'], $text)) {
                return $created;
            }
        }

        throw new \RuntimeException('No matching log found');
    }
}
