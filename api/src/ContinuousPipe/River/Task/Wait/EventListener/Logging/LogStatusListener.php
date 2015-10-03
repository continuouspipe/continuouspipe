<?php

namespace ContinuousPipe\River\Task\Wait\EventListener\Logging;

use ContinuousPipe\River\Task\Wait\Event\WaitEvent;
use ContinuousPipe\River\Task\Wait\Event\WaitFailed;
use ContinuousPipe\River\Task\Wait\Event\WaitStarted;
use ContinuousPipe\River\Task\Wait\Event\WaitSuccessful;
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
            $logger->start();
        } elseif ($event instanceof WaitFailed) {
            $logger->failure();
        } elseif ($event instanceof WaitSuccessful) {
            $logger->success();
        }
    }
}
