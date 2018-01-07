<?php

namespace ContinuousPipe\River\Bridge\Pipe;

use ContinuousPipe\River\Bridge\Pipe\Command\PipeDeploymentFinishedCommand;
use ContinuousPipe\Pipe\Event\DeploymentEvent;
use SimpleBus\Message\Bus\MessageBus;

class ListenForDeploymentStatus
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    public function __construct(MessageBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function handle(DeploymentEvent $event)
    {
        $this->commandBus->handle(new PipeDeploymentFinishedCommand(
            $event->getDeploymentUuid()
        ));
    }
}
