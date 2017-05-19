<?php


namespace ContinuousPipe\Builder\Aggregate\GoogleContainerBuilder\EventListener;

use ContinuousPipe\Builder\Aggregate\GoogleContainerBuilder\Command\FetchGCBuildStatus;
use ContinuousPipe\Builder\Aggregate\GoogleContainerBuilder\Event\GCBuildStarted;
use SimpleBus\Message\Bus\MessageBus;

class FetchStatusWhenGCBuildStarted
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

    public function notify(GCBuildStarted $event)
    {
        $this->commandBus->handle(new FetchGCBuildStatus(
            $event->getBuildIdentifier(),
            $event->getBuild()
        ));
    }
}