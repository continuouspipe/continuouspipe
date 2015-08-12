<?php

namespace ContinuousPipe\River\Tests\Logging;

use LogStream\EmptyLogger;
use LogStream\Log;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Container;

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
        $log = new MutableWrappedLog(uniqid(), new Container());

        $this->logStore->store($log);

        return new InMemoryLogger(new EmptyLogger($log), $this->logStore);
    }

    /**
     * {@inheritdoc}
     */
    public function from(Log $parent)
    {
        return new InMemoryLogger(new EmptyLogger($parent), $this->logStore);
    }

    /**
     * {@inheritdoc}
     */
    public function fromId($parentId)
    {
        return $this->from(
            $this->logStore->findById($parentId)
        );
    }
}
