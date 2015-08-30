<?php

namespace ContinuousPipe\Pipe\Logging;

use ContinuousPipe\Pipe\View\Deployment;
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

        if ($notification = $request->getNotification()) {
            if ($parentLogId = $notification->getLogStreamParentId()) {
                return $this->loggerFactory->fromId($parentLogId);
            }
        }

        return $this->loggerFactory->create();
    }
}
