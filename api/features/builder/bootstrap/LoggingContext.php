<?php

use Behat\Behat\Context\Context;
use LogStream\Log;
use LogStream\Tests\InMemoryLogClient;
use LogStream\TraceableClient;

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
     * @param TraceableClient $traceableClient
     * @param InMemoryLogClient $inMemoryLogClient
     */
    public function __construct(TraceableClient $traceableClient, InMemoryLogClient $inMemoryLogClient)
    {
        $this->traceableClient = $traceableClient;
        $this->inMemoryLogClient = $inMemoryLogClient;
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
