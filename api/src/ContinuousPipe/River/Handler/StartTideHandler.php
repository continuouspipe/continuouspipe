<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Tide;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

class StartTideHandler
{
    /**
     * @var MessageBus
     */
    private $eventBus;
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param MessageBus     $eventBus
     * @param TideRepository $tideRepository
     * @param LoggerFactory  $loggerFactory
     */
    public function __construct(MessageBus $eventBus, TideRepository $tideRepository, LoggerFactory $loggerFactory)
    {
        $this->eventBus = $eventBus;
        $this->loggerFactory = $loggerFactory;
        $this->tideRepository = $tideRepository;
    }

    /**
     * @param StartTideCommand $command
     */
    public function handle(StartTideCommand $command)
    {
        $tide = $this->tideRepository->find($command->getTideUuid());

        $logger = $this->loggerFactory->from($tide->getContext()->getLog());
        $logger->child(new Text('Starting Tide'));

        $this->eventBus->handle(new TideStarted($command->getTideUuid()));
    }
}
