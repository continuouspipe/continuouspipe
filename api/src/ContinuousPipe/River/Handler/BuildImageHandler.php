<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\Builder\Client\BuilderClient;
use ContinuousPipe\River\Command\BuildImageCommand;
use ContinuousPipe\River\Event\Build\ImageBuildStarted;
use SimpleBus\Message\Bus\MessageBus;

class BuildImageHandler
{
    /**
     * @var BuilderClient
     */
    private $builderClient;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param BuilderClient $builderClient
     * @param MessageBus $eventBus
     */
    public function __construct(BuilderClient $builderClient, MessageBus $eventBus)
    {
        $this->builderClient = $builderClient;
        $this->eventBus = $eventBus;
    }

    /**
     * @param BuildImageCommand $command
     */
    public function handle(BuildImageCommand $command)
    {
        $build = $this->builderClient->build($command->getBuildRequest());

        $this->eventBus->handle(new ImageBuildStarted($command->getTideUuid(), $build));
    }
}
