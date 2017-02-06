<?php

namespace ContinuousPipe\Builder\Logging;

use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFailed;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Psr\Log\LoggerInterface;

class DisplayTheErrors
{
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerFactory $loggerFactory, LoggerInterface $logger)
    {
        $this->loggerFactory = $loggerFactory;
        $this->logger = $logger;
    }

    public function notify(StepFailed $event)
    {
        $logger = $this->loggerFactory->fromId($event->getLogStreamIdentifier());
        $child = $logger->child(new Text(
            $event->getReason()->getMessage()
        ));

        $this->logger->warning('A build failed with a exception', [
            'exception' => $event->getReason(),
        ]);

        $child->updateStatus(Log::FAILURE);
    }
}
