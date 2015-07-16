<?php

namespace ContinuousPipe\River\EventListener\External;

use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\Event\External\CodePushedEvent;
use ContinuousPipe\River\Tide;
use League\Tactician\CommandBus;

class CodePushedListener
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param CodePushedEvent $event
     */
    public function notify(CodePushedEvent $event)
    {
        $this->commandBus->handle(new StartTideCommand());
    }
}
