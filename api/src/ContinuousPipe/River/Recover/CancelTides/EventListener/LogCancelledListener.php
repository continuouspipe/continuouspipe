<?php

namespace ContinuousPipe\River\Recover\CancelTides\EventListener;

use ContinuousPipe\River\Recover\CancelTides\Event\TideCancelled;
use ContinuousPipe\River\View\TideRepository;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

class LogCancelledListener
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param TideRepository $tideRepository
     * @param LoggerFactory  $loggerFactory
     */
    public function __construct(TideRepository $tideRepository, LoggerFactory $loggerFactory)
    {
        $this->tideRepository = $tideRepository;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param TideCancelled $event
     */
    public function notify(TideCancelled $event)
    {
        $tide = $this->tideRepository->find($event->getTideUuid());
        $logger = $this->loggerFactory->fromId($tide->getLogId());

        $logger->child(new Text('Tide manually cancelled'))->updateStatus(Log::FAILURE);
    }
}
