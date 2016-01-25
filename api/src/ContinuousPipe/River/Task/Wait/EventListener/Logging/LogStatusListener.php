<?php

namespace ContinuousPipe\River\Task\Wait\EventListener\Logging;

use ContinuousPipe\River\Task\Wait\Event\WaitEvent;
use ContinuousPipe\River\Task\Wait\Event\WaitFailed;
use ContinuousPipe\River\Task\Wait\Event\WaitStarted;
use ContinuousPipe\River\Task\Wait\Event\WaitSuccessful;
use LogStream\Log;
use LogStream\LoggerFactory;

class LogStatusListener
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
     * @param WaitEvent $event
     */
    public function notify(WaitEvent $event)
    {
        if ($event instanceof WaitStarted) {
            $log = $event->getLog();
        } elseif ($event instanceof WaitFailed || $event instanceof WaitSuccessful) {
            $log = $event->getWaitStarted()->getLog();
        } else {
            throw new \InvalidArgumentException(sprintf(
                'No supporting events of type "%s"',
                get_class($event)
            ));
        }

        $logger = $this->loggerFactory->from($log);
        if ($event instanceof WaitStarted) {
            $logger->updateStatus(Log::RUNNING);
        } elseif ($event instanceof WaitFailed) {
            $logger->updateStatus(Log::FAILURE);
        } elseif ($event instanceof WaitSuccessful) {
            $logger->updateStatus(Log::SUCCESS);
        }
    }
}
