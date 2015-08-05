<?php

namespace ContinuousPipe\River\EventListener\Logging;

use ContinuousPipe\River\Event\ImageBuildsFailed;
use ContinuousPipe\River\Event\ImagesBuilt;
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
        } else if ($event instanceof ImagesBuilt) {
            $this->loggerFactory->from($event->getLog())->success();
        }
    }
}
