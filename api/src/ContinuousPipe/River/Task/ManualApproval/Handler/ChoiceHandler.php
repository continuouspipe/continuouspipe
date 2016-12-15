<?php

namespace ContinuousPipe\River\Task\ManualApproval\Handler;

use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Task\ManualApproval\Command\Approve;
use ContinuousPipe\River\Task\ManualApproval\Command\ManualApprovalCommand;
use ContinuousPipe\River\Task\ManualApproval\Command\Reject;
use ContinuousPipe\River\Task\ManualApproval\ManualApprovalTask;
use LogStream\LoggerFactory;
use SimpleBus\Message\Bus\MessageBus;

class ChoiceHandler
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var MessageBus
     */
    private $eventBus;
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param TideRepository $tideRepository
     * @param MessageBus     $eventBus
     * @param LoggerFactory  $loggerFactory
     */
    public function __construct(TideRepository $tideRepository, MessageBus $eventBus, LoggerFactory $loggerFactory)
    {
        $this->tideRepository = $tideRepository;
        $this->eventBus = $eventBus;
        $this->loggerFactory = $loggerFactory;
    }

    public function handle(ManualApprovalCommand $command)
    {
        $tide = $this->tideRepository->find($command->getTideUuid());
        $task = $tide->getTask($command->getTaskIdentifier());

        if (!$task instanceof ManualApprovalTask) {
            throw new \InvalidArgumentException(sprintf('The task "%s" is not a manual approval task', $command->getTaskIdentifier()));
        }

        if ($command instanceof Approve) {
            $task->approve($this->loggerFactory, $command->getUser());
        } elseif ($command instanceof Reject) {
            $task->reject($this->loggerFactory, $command->getUser());
        }

        $events = $tide->popNewEvents();
        foreach ($events as $newEvent) {
            $this->eventBus->handle($newEvent);
        }
    }
}
