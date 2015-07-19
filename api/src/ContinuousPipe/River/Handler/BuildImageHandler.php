<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\Builder\Client\BuilderClient;
use ContinuousPipe\River\Command\BuildImageCommand;
use ContinuousPipe\River\Event\Build\ImageBuildStarted;
use ContinuousPipe\River\Repository\TideRepository;
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
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param BuilderClient  $builderClient
     * @param TideRepository $tideRepository
     * @param MessageBus     $eventBus
     */
    public function __construct(BuilderClient $builderClient, TideRepository $tideRepository, MessageBus $eventBus)
    {
        $this->builderClient = $builderClient;
        $this->eventBus = $eventBus;
        $this->tideRepository = $tideRepository;
    }

    /**
     * @param BuildImageCommand $command
     */
    public function handle(BuildImageCommand $command)
    {
        $tide = $this->tideRepository->find($command->getTideUuid());
        $build = $this->builderClient->build($command->getBuildRequest(), $tide->getUser());

        $this->eventBus->handle(new ImageBuildStarted($command->getTideUuid(), $build));
    }
}
