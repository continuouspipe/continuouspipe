<?php

namespace ContinuousPipe\River\Task\Run\EventListener\Logging;

use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\Run\Event\RunFailed;
use ContinuousPipe\River\Task\Run\Event\RunSuccessful;
use LogStream\LoggerFactory;

class RunStatusListener
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
     * @param TideEvent $event
     */
    public function notify(TideEvent $event)
    {
        if ($event instanceof RunFailed) {
            $this->getLogger($event->getDeployment())->failure();
        } elseif ($event instanceof RunSuccessful) {
            $this->getLogger($event->getDeployment())->success();
        }
    }

    /**
     * @param Deployment $deployment
     *
     * @return \LogStream\Logger
     */
    private function getLogger(Deployment $deployment)
    {
        $parentLogId = $deployment->getRequest()->getNotification()->getLogStreamParentId();

        return $this->loggerFactory->fromId($parentLogId);
    }
}
