<?php

namespace ContinuousPipe\River\Flex\AsFeature;

use ContinuousPipe\River\Flex\AsFeature\Command\ActivateFlex;
use ContinuousPipe\River\Flow\Event\FlowCreated;
use SimpleBus\Message\Bus\MessageBus;

class ActivateFlexWhenFlowIsCreated
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

    public function notify(FlowCreated $event)
    {
        $this->commandBus->handle(new ActivateFlex($event->getFlowUuid()));
    }
}
