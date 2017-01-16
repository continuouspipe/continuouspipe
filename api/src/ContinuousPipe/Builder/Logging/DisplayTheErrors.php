<?php

namespace ContinuousPipe\Builder\Logging;

use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFailed;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

class DisplayTheErrors
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

    public function notify(StepFailed $event)
    {
        $logger = $this->loggerFactory->fromId($event->getLogStreamIdentifier());
        $child = $logger->child(new Text(
            $event->getReason()->getMessage()
        ));

        $child->updateStatus(Log::FAILURE);
    }
}
