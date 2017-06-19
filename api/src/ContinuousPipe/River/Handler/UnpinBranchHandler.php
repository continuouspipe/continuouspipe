<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\River\Command\UnpinBranch;
use ContinuousPipe\River\Repository\FlowRepository;
use SimpleBus\Message\Bus\MessageBus;

class UnpinBranchHandler
{
    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @var MessageBus
     */
    private $eventBus;

    public function __construct(FlowRepository $flowRepository, MessageBus $eventBus)
    {
        $this->flowRepository = $flowRepository;
        $this->eventBus = $eventBus;
    }

    public function handle(UnpinBranch $command)
    {
        $flow = $this->flowRepository->find($command->getFlowUuid());
        $flow->unpinBranch($command->getBranch());

        foreach ($flow->raisedEvents() as $event) {
            $this->eventBus->handle($event);
        }
    }
}
