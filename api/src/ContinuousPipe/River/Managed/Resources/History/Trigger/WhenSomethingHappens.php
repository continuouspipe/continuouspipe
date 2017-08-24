<?php

namespace ContinuousPipe\River\Managed\Resources\History\Trigger;

use ContinuousPipe\River\Flow\Event\FlowEvent;
use ContinuousPipe\River\Managed\Resources\History\Command\GetAndStoreResourceUsageCommand;
use SimpleBus\Message\Bus\MessageBus;

class WhenSomethingHappens
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

    public function notify(FlowEvent $event)
    {
        $this->commandBus->handle(new GetAndStoreResourceUsageCommand(
            $event->getFlowUuid()
        ));
    }
}
