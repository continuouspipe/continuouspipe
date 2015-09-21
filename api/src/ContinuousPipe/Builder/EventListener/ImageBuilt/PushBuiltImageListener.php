<?php

namespace ContinuousPipe\Builder\EventListener\ImageBuilt;

use ContinuousPipe\Builder\Command\PushImageCommand;
use ContinuousPipe\Builder\Event\ImageBuilt;
use SimpleBus\Message\Bus\MessageBus;

class PushBuiltImageListener
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param MessageBus $commandBus
     */
    public function __construct(MessageBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param ImageBuilt $event
     */
    public function notify(ImageBuilt $event)
    {
        $this->commandBus->handle(new PushImageCommand(
            $event->getBuild(),
            $event->getLogger()
        ));
    }
}
