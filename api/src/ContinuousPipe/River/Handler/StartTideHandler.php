<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\Tide;
use SimpleBus\Message\Bus\MessageBus;

class StartTideHandler
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param MessageBus $eventBus
     */
    public function __construct(MessageBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @param StartTideCommand $command
     */
    public function handle(StartTideCommand $command)
    {
        $flow = $command->getFlow();
        $tide = Tide::create($command->getUuid(), $flow, $command->getCodeReference(), $command->getParentLog());

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }
    }
}
