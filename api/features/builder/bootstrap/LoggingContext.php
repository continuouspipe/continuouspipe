<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Builder\Reporting\TracedPublisher;
use ContinuousPipe\Events\TimeResolver\PredictableTimeResolver;
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
    /**
     * @var PredictableTimeResolver
     */
    private $predictableTimeResolver;

    public function __construct(TraceableClient $traceableClient, InMemoryLogClient $inMemoryLogClient, TracedPublisher $tracedPublisher, PredictableTimeResolver $predictableTimeResolver)
    {
        $this->traceableClient = $traceableClient;
        $this->inMemoryLogClient = $inMemoryLogClient;
        $this->tracedPublisher = $tracedPublisher;
        $this->predictableTimeResolver = $predictableTimeResolver;
    }

    /**
     * @Transform :datetime
     */
    public function transformDateTime($value)
    {
        return \DateTime::createFromFormat(\DateTime::ISO8601, $value);
    }

    /**
     * @When the current datetime is :datetime
     */
    public function theCurrentDatetimeIs(\DateTime $datetime)
    {
        $this->predictableTimeResolver->setCurrent($datetime);
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
            $foundValue = $propertyAccessor->getValue($report, '['.str_replace('.', '][', $key).']');

            if ($foundValue == $value) {
                return;
            }
        }

        throw new \RuntimeException('Value not found');
    }

    /**
     * @Then the published report should contain the key :key
     */
    public function thePublishedReportShouldContainTheKey($key)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($this->tracedPublisher->getPublishedReports() as $report) {
            $value = $propertyAccessor->getValue($report, '['.str_replace('.', '][', $key).']');

            if (!empty($value)) {
                return;
            }
        }

        throw new \RuntimeException(sprintf(
            'Value was not found'
        ));
    }

    /**
     * @Then a log containing :text should be created
     */
    public function aLogContainingShouldBeCreated($text)
    {
        return $this->findLogContaining($this->traceableClient->getCreated(), $text);
    }

    /**
     * @Then a log containing :text should be created once
     */
    public function aLogContainingShouldBeCreatedOnce($text)
    {
        $count = count($this->findAllLogsContaining($this->traceableClient->getCreated(), $text));
        if (1 !== $count) {
            throw new \RuntimeException(sprintf('Expected to find the text one, but found %d times.', $count));
        }
    }

    /**
     * @Then a log containing :text should not be found
     */
    public function aLogContainingShouldNotBeFound($text)
    {
        $count = count($this->findAllLogsContaining($this->traceableClient->getCreated(), $text));
        if (0 !== $count) {
            throw new \RuntimeException(sprintf('The text found %d times, but not expected.', $count));
        }
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

    /**
     * @param Log[] $logs
     * @param string $text
     *
     * @return Log[]
     */
    private function findAllLogsContaining(array $logs, string $text): array
    {
        $list = [];
        foreach ($logs as $created) {
            $serialized = $created->getNode()->jsonSerialize();

            if ($serialized['type'] == 'text' && isset($serialized['contents']) && false !== strpos($serialized['contents'], $text)) {
                $list[] = $created;
            }
        }

        return $list;
    }
}
