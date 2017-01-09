<?php

namespace ContinuousPipe\River\Task\Wait;

use ContinuousPipe\River\Event\GitHub\StatusUpdated;
use ContinuousPipe\River\Repository\TideRepository;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;

class WaitSaga
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

    public function notify(StatusUpdated $statusUpdated)
    {
        $tide = $this->tideRepository->find($statusUpdated->getTideUuid());
        $tasks = $tide->getTasks()->ofType(WaitTask::class);

        foreach ($tasks as $task) {
            $task->statusUpdated($statusUpdated);
        }

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }
    }
}
