<?php

namespace ContinuousPipe\Runner\Client;

use ContinuousPipe\Runner\Client\Logging\LogStream;
use JMS\Serializer\Annotation as JMS;

class Logging
{
    /**
     * @JMS\Type("ContinuousPipe\Running\Client\Logging\LogStream")
     *
     * @var LogStream
     */
    private $logStream;

    /**
     * @param LogStream $logStream
     */
    public function __construct(LogStream $logStream)
    {
        $this->logStream = $logStream;
    }

    /**
     * @return LogStream
     */
    public function getLogStream()
    {
        return $this->logStream;
    }
}
