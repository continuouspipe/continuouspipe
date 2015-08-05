<?php

namespace ContinuousPipe\River\EventListener\Logging;

use ContinuousPipe\River\Event\Build\BuildEvent;
use ContinuousPipe\River\Event\Build\BuildFailed;
use ContinuousPipe\River\Event\Build\BuildSuccessful;
use LogStream\LoggerFactory;
use LogStream\WrappedLog;
use Symfony\Component\DependencyInjection\Container;

class BuildListener
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
     * @param BuildEvent $event
     */
    public function notify(BuildEvent $event)
    {
        $request = $event->getBuild()->getRequest();
        $logIdentifier = $request->getLogging()->getLogStream()->getParentLogIdentifier();

        $logger = $this->loggerFactory->from(new WrappedLog($logIdentifier));
        if ($event instanceof BuildSuccessful) {
            $logger->success();
        } else if ($event instanceof BuildFailed) {
            $logger->failure();
        }
    }
}
