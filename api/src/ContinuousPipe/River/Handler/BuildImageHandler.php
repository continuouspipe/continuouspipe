<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\Builder\Client\BuilderClient;
use ContinuousPipe\River\Command\BuildImageCommand;
use ContinuousPipe\River\Event\Build\BuildFailed;
use ContinuousPipe\River\Event\Build\BuildSuccessful;
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
        $tideUuid = $command->getTideUuid();
        $tide = $this->tideRepository->find($tideUuid);
        $build = $this->builderClient->build($command->getBuildRequest(), $tide->getUser());

        $this->eventBus->handle(new ImageBuildStarted($tideUuid, $build));

        // Sent events as if they were sent form external if the build as already
        // a success or failure status
        if ($build->isSuccessful()) {
            $this->eventBus->handle(new BuildSuccessful($tideUuid, $build));
        } elseif ($build->isErrored()) {
            $this->eventBus->handle(new BuildFailed($tideUuid, $build));
        }
    }
}
