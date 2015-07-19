<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\River\Command\BuildImageCommand;
use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Tide;
use League\Tactician\CommandBus;
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
        $repository = $command->getRepository();
        $tide = Tide::createFromRepository($repository);

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }
    }
}
