<?php

namespace ContinuousPipe\Builder\Aggregate\GoogleContainerBuilder\CommandHandler;

use ContinuousPipe\Builder\Aggregate\GoogleContainerBuilder\Command\FetchGCBuildStatus;
use ContinuousPipe\Builder\Aggregate\GoogleContainerBuilder\Event\GCBuildFinished;
use ContinuousPipe\Builder\GoogleContainerBuilder\GoogleContainerBuilderClient;
use SimpleBus\Message\Bus\MessageBus;

class FetchGCBuildStatusHandler
{
    /**
     * @var GoogleContainerBuilderClient
     */
    private $containerBuilderClient;

    /**
     * @var MessageBus
     */
    private $eventBus;
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param GoogleContainerBuilderClient $containerBuilderClient
     * @param MessageBus $eventBus
     * @param MessageBus $commandBus
     */
    public function __construct(GoogleContainerBuilderClient $containerBuilderClient, MessageBus $eventBus, MessageBus $commandBus)
    {
        $this->containerBuilderClient = $containerBuilderClient;
        $this->eventBus = $eventBus;
        $this->commandBus = $commandBus;
    }

    public function handle(FetchGCBuildStatus $command)
    {
        $status = $this->containerBuilderClient->fetchStatus($command->getGoogleContainerBuild());

        if ($status->isRunning()) {
            // Sleep 2 seconds, to throttle the messages, until we find
            // a better option.
            sleep(2);

            $this->commandBus->handle(new FetchGCBuildStatus(
                $command->getBuildIdentifier(),
                $command->getGoogleContainerBuild()
            ));

            return;
        }

        $this->eventBus->handle(new GCBuildFinished(
            $command->getBuildIdentifier(),
            $status
        ));
    }
}
