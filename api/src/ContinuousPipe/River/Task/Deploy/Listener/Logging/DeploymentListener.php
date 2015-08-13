<?php

namespace ContinuousPipe\River\Task\Deploy\Listener\Logging;

use ContinuousPipe\River\Task\Deploy\Event\DeploymentEvent;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentFailed;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use LogStream\LoggerFactory;

class DeploymentListener
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
     * @param DeploymentEvent $event
     */
    public function notify(DeploymentEvent $event)
    {
        if ($event instanceof DeploymentFailed) {
            $this->getLogger($event)->failure();
        } elseif ($event instanceof DeploymentSuccessful) {
            $this->getLogger($event)->success();
        }
    }

    /**
     * @param DeploymentEvent $event
     *
     * @return \LogStream\Logger
     */
    private function getLogger(DeploymentEvent $event)
    {
        return $this->loggerFactory->fromId($event->getDeployment()->getRequest()->getLogId());
    }
}
