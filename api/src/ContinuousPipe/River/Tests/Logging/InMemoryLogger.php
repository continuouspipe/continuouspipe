<?php

namespace ContinuousPipe\River\Tests\Logging;

use LogStream\EmptyLogger;
use LogStream\Log;
use LogStream\Logger;
use LogStream\LogNode;

class InMemoryLogger implements Logger
{
    /**
     * @var EmptyLogger
     */
    private $emptyLogger;

    /**
     * @var InMemoryLogStore
     */
    private $logStore;

    /**
     * @param EmptyLogger      $emptyLogger
     * @param InMemoryLogStore $logStore
     */
    public function __construct(EmptyLogger $emptyLogger, InMemoryLogStore $logStore)
    {
        $this->emptyLogger = $emptyLogger;
        $this->logStore = $logStore;
    }

    /**
     * {@inheritdoc}
     */
    public function append(LogNode $log)
    {
        $log = $this->emptyLogger->append($log);
        $log = new MutableWrappedLog($log->getId(), $log->getNode(), $log->getStatus());
        $this->logStore->store($log, $this->emptyLogger->getLog());

        return $log;
    }

    /**
     * Update the log status to running.
     */
    public function start()
    {
        $this->updatesStatus(Log::RUNNING);
    }

    /**
     * @return Log|null
     */
    public function getLog()
    {
        return $this->emptyLogger->getLog();
    }

    /**
     * Update the log status to success.
     */
    public function success()
    {
        $this->updatesStatus(Log::SUCCESS);
    }

    /**
     * Update the log status to failure.
     */
    public function failure()
    {
        $this->updatesStatus(Log::FAILURE);
    }

    /**
     * @param string $status
     */
    private function updatesStatus($status)
    {
        $log = $this->emptyLogger->getLog();

        if ($log instanceof MutableWrappedLog) {
            $log->setStatus($status);
        } else {
            throw new \RuntimeException('Non-mutable log found');
        }
    }
}
