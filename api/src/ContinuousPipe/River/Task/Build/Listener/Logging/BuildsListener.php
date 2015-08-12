<?php

namespace ContinuousPipe\River\Task\Build\Listener\Logging;

use ContinuousPipe\River\Task\Build\Event\ImageBuildsFailed;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsSuccessful;
use LogStream\LoggerFactory;

class BuildsListener
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
            $this->loggerFactory->from($event->getLog())->failure();
        } elseif ($event instanceof ImageBuildsSuccessful) {
            $this->loggerFactory->from($event->getLog())->success();
        }
    }
}
