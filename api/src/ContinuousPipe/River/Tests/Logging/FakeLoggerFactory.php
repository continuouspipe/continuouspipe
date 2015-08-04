<?php

namespace ContinuousPipe\River\Tests\Logging;

use LogStream\EmptyLogger;
use LogStream\Log;
use LogStream\Logger;
use LogStream\LoggerFactory;

class FakeLoggerFactory implements LoggerFactory
{
    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return new EmptyLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function from(Log $parent)
    {
        return new EmptyLogger($parent);
    }
}
