<?php

namespace ContinuousPipe\River\EventListener\TaskSkipped;

use ContinuousPipe\River\Task\TaskSkipped;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

class LoggingListener
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
     * @param TaskSkipped $event
     */
    public function notify(TaskSkipped $event)
    {
        $logger = $this->loggerFactory->fromId($event->getTaskLogId());

        $logger->child(new Text(sprintf(
            'Skipping task "%s" based on filters',
            $event->getTaskId()
        )));
    }
}
