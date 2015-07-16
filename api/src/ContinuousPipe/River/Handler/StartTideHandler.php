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

    public function __construct(MessageBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    public function handle(StartTideCommand $command)
    {
        $tide = Tide::create();

        $startEvent = new TideStarted($tide);
        $tide->apply($startEvent);

        $this->eventBus->handle($startEvent);
    }
}
