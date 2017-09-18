<?php

namespace ContinuousPipe\River\Recover\CancelTides\EventListener;

use ContinuousPipe\River\Event\TideCancelled;
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
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(TideRepository $tideRepository, LoggerFactory $loggerFactory)
    {
        $this->tideRepository = $tideRepository;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param \ContinuousPipe\River\Event\TideCancelled $event
     */
    public function notify(TideCancelled $event)
    {
        $tide = $this->tideRepository->find($event->getTideUuid());
        $logger = $this->loggerFactory->fromId($tide->getLogId());
        $username = $event->getUser()->getUsername();

        $logger->child(new Text(sprintf('Tide manually cancelled by %s', $username)))->updateStatus(Log::FAILURE);
    }
}
