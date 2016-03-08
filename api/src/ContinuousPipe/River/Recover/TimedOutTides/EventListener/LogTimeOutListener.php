<?php

namespace ContinuousPipe\River\Recover\TimedOutTides\EventListener;

use ContinuousPipe\River\Recover\TimedOutTides\Event\TideTimedOut;
use ContinuousPipe\River\View\TideRepository;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

class LogTimeOutListener
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
     * @param TideTimedOut $event
     */
    public function notify(TideTimedOut $event)
    {
        $tide = $this->tideRepository->find($event->getTideUuid());
        $logger = $this->loggerFactory->fromId($tide->getLogId());
        $logger->child(new Text('Time out'))->updateStatus(Log::FAILURE);
    }
}
