<?php

namespace ContinuousPipe\Builder\EventListener\ImagePushed;

use ContinuousPipe\Builder\Event\BuildSuccessful;
use ContinuousPipe\Builder\Event\ImagePushed;
use SimpleBus\Message\Bus\MessageBus;

class BuildIsSuccessfulAfterImagePushed
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
     * @param ImagePushed $event
     */
    public function notify(ImagePushed $event)
    {
        $this->eventBus->handle(new BuildSuccessful(
            $event->getBuild()
        ));
    }
}
