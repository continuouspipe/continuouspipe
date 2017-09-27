<?php

namespace ContinuousPipe\Builder\Logging;

use ContinuousPipe\Builder\Build;
use LogStream\LoggerFactory;

class BuildLoggerFactory
{
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param Build $build
     *
     * @return \LogStream\Logger
     */
    public function forBuild(Build $build)
    {
        if ($logging = $build->getRequest()->getLogging()) {
            if ($logStream = $logging->getLogstream()) {
                return $this->loggerFactory->fromId($logStream->getParentLogIdentifier());
            }
        }

        return $this->loggerFactory->create();
    }
}
