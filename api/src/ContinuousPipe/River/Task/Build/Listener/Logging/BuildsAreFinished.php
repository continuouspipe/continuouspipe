<?php

namespace ContinuousPipe\River\Task\Build\Listener\Logging;

use ContinuousPipe\River\Task\Build\Event\ImageBuildsFailed;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsSuccessful;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

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
            $logger = $this->loggerFactory->from($event->getLog())
                ->updateStatus(Log::FAILURE)
            ;

            if (null !== ($reason = $event->getReason())) {
                $logger->child(new Text($reason))->updateStatus(Log::FAILURE);
            }
        } elseif ($event instanceof ImageBuildsSuccessful) {
            $this->loggerFactory->from($event->getLog())->updateStatus(Log::SUCCESS);
        }
    }
}
