<?php

namespace ContinuousPipe\River\EventListener;

use ContinuousPipe\River\Event\TideStarted;
use League\Tactician\CommandBus;

class TideStartedHandler
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function notify(TideStarted $event)
    {
        $images = $this->getImagesToBuild();
        foreach ($images as $image) {
            $build = $this->commandBus->handle(new BuildImageCommand());
        }
    }
}
