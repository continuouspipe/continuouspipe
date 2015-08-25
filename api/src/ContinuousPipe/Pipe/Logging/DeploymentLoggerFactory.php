<?php

namespace ContinuousPipe\Pipe\Logging;

use ContinuousPipe\Pipe\Deployment;
use LogStream\EmptyLogger;
use LogStream\LoggerFactory;

class DeploymentLoggerFactory
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
     * @param Deployment $deployment
     *
     * @return \LogStream\Logger
     */
    public function create(Deployment $deployment)
    {
        $request = $deployment->getRequest();

        if ($request->getLogId()) {
            return $this->loggerFactory->fromId(
                $request->getLogId()
            );
        }

        return $this->loggerFactory->create();
    }
}
