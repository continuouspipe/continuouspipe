<?php

namespace ContinuousPipe\Builder;

class Logging
{
    /**
     * @var LogStreamLogging
     */
    private $logStream;

    /**
     * @param LogStreamLogging $logStreamLogging
     *
     * @return Logging
     */
    public static function withLogStream(LogStreamLogging $logStreamLogging)
    {
        $logging = new self();
        $logging->logStream = $logStreamLogging;

        return $logging;
    }

    /**
     * @return LogStreamLogging
     */
    public function getLogStream()
    {
        return $this->logStream;
    }
}
