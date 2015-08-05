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
     * @param EmptyLogger $emptyLogger
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

        $this->logStore->store($log, $this->emptyLogger->getLog());

        return new MutableWrappedLog($log->getId(), $log->getNode(), $log->getStatus());
    }

    /**
     * Update the log status to running.
     */
    public function start()
    {
        $log = $this->emptyLogger->getLog();

        if ($log instanceof MutableWrappedLog) {
            $log->setStatus(Log::RUNNING);
        }
    }

    /**
     * @return Log|null
     */
    public function getLog()
    {
        return $this->emptyLogger->getLog();
    }
}
