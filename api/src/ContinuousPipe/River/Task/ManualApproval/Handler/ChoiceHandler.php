<?php

namespace ContinuousPipe\River\Task\ManualApproval\Handler;

use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Task\ManualApproval\Command\Approve;
use ContinuousPipe\River\Task\ManualApproval\Command\ManualApprovalCommand;
use ContinuousPipe\River\Task\ManualApproval\Command\Reject;
use ContinuousPipe\River\Task\ManualApproval\ManualApprovalTask;
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
     * @param TideRepository $tideRepository
     * @param MessageBus     $eventBus
     */
    public function __construct(TideRepository $tideRepository, MessageBus $eventBus)
    {
        $this->tideRepository = $tideRepository;
        $this->eventBus = $eventBus;
    }

    public function handle(ManualApprovalCommand $command)
    {
        $tide = $this->tideRepository->find($command->getTideUuid());
        $task = $tide->getTask($command->getTaskIdentifier());

        if (!$task instanceof ManualApprovalTask) {
            throw new \InvalidArgumentException(sprintf('The task "%s" is not a manual approval task', $command->getTaskIdentifier()));
        }

        if ($command instanceof Approve) {
            $task->approve($command->getUser());
        } elseif ($command instanceof Reject) {
            $task->reject($command->getUser());
        }

        $events = $tide->popNewEvents();
        foreach ($events as $newEvent) {
            $this->eventBus->handle($newEvent);
        }
    }
}
