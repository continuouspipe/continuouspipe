<?php

namespace ContinuousPipe\River\EventListener;

use ContinuousPipe\River\Command\BuildImagesCommand;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Repository\TideRepository;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

class TideStartedListener
{
    /**
     * @var MessageBus
     */
    private $commandBus;
    /**
     * @var TideRepository
     */
    private $tideRepository;
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param MessageBus $commandBus
     * @param TideRepository $tideRepository
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(MessageBus $commandBus, TideRepository $tideRepository, LoggerFactory $loggerFactory)
    {
        $this->commandBus = $commandBus;
        $this->tideRepository = $tideRepository;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param TideStarted $event
     */
    public function notify(TideStarted $event)
    {
        $tideUuid = $event->getTideUuid();
        $tide = $this->tideRepository->find($tideUuid);

        $logger = $this->loggerFactory->from($tide->getParentLog());
        $log = $logger->append(new Text('Building application images'));

        $this->commandBus->handle(new BuildImagesCommand($event->getTideUuid(), $log));
    }
}
