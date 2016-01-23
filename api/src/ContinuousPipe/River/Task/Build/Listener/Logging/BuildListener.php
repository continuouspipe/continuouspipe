<?php

namespace ContinuousPipe\River\Task\Build\Listener\Logging;

use ContinuousPipe\River\Task\Build\Event\BuildEvent;
use ContinuousPipe\River\Task\Build\Event\BuildFailed;
use ContinuousPipe\River\Task\Build\Event\BuildSuccessful;
use LogStream\Log;
use LogStream\LoggerFactory;

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
        if (null === ($request = $event->getBuild()->getRequest())) {
            return;
        }

        $logIdentifier = $request->getLogging()->getLogStream()->getParentLogIdentifier();
        $logger = $this->loggerFactory->fromId($logIdentifier);

        if ($event instanceof BuildSuccessful) {
            $logger->updateStatus(Log::SUCCESS);
        } elseif ($event instanceof BuildFailed) {
            $logger->updateStatus(Log::FAILURE);
        }
    }
}
