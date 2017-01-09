<?php

namespace ContinuousPipe\River\Task\Build;

use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Task\Build\Command\ReceiveBuildNotification;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;

class BuildSaga
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param TideRepository  $tideRepository
     * @param LoggerInterface $logger
     * @param MessageBus      $eventBus
     */
    public function __construct(TideRepository $tideRepository, LoggerInterface $logger, MessageBus $eventBus)
    {
        $this->tideRepository = $tideRepository;
        $this->logger = $logger;
        $this->eventBus = $eventBus;
    }

    public function handle(ReceiveBuildNotification $command)
    {
        $tide = $this->tideRepository->find($command->getTideUuid());
        $tasks = $tide->getTasks()->ofType(BuildTask::class);

        foreach ($tasks as $task) {
            $task->receiveBuildNotification($command->getBuild());
        }

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }
    }
}
