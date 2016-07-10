<?php

namespace ContinuousPipe\River\Task\Build\Listener\Logging;

use ContinuousPipe\River\Task\Build\Event\ImageBuildsFailed;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsSuccessful;
use LogStream\Log;
use LogStream\LoggerFactory;

class BuildsAreFinished
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

    public function notify($event)
    {
        if ($event instanceof ImageBuildsFailed) {
            $this->loggerFactory->from($event->getLog())->updateStatus(Log::FAILURE);
        } elseif ($event instanceof ImageBuildsSuccessful) {
            $this->loggerFactory->from($event->getLog())->updateStatus(Log::SUCCESS);
        }
    }
}
