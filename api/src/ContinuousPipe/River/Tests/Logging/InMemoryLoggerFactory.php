<?php

namespace ContinuousPipe\River\Tests\Logging;

use LogStream\EmptyLogger;
use LogStream\Log;
use LogStream\LoggerFactory;

class InMemoryLoggerFactory implements LoggerFactory
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
     * {@inheritdoc}
     */
    public function create()
    {
        return new InMemoryLogger(new EmptyLogger(), $this->logStore);
    }

    /**
     * {@inheritdoc}
     */
    public function from(Log $parent)
    {
        return new InMemoryLogger(new EmptyLogger($parent), $this->logStore);
    }
}
