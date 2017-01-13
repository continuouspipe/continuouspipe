<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\CodeArchiveCreated;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\DockerImageBuilt;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepEvent;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepStarted;
use ContinuousPipe\Builder\Aggregate\Event\BuildStepStarted;
use ContinuousPipe\Builder\Archive\ArchiveCreationException;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\BuildException;
use ContinuousPipe\Builder\Docker\DockerException;
use ContinuousPipe\Builder\Docker\DockerFacade;
use ContinuousPipe\Builder\Request\ArchiveSource;
use ContinuousPipe\Events\Transaction\TransactionManager;
use Http\Client\Common\Plugin\DecoderPlugin;
use SimpleBus\Message\Bus\MessageBus;

class BuildStepSaga
{
    /**
     * @var BuildStepRepository
     */
    private $buildStepRepository;
    /**
     * @var MessageBus
     */
    private $eventBus;
    /**
     * @var ArchiveBuilder
     */
    private $archiveBuilder;
    /**
     * @var DockerFacade
     */
    private $dockerFacade;

    public function __construct(
        BuildStepRepository $buildStepRepository,
        MessageBus $eventBus,
        ArchiveBuilder $archiveBuilder,
        DockerFacade $dockerFacade
    ) {
        $this->buildStepRepository = $buildStepRepository;
        $this->eventBus = $eventBus;
        $this->archiveBuilder = $archiveBuilder;
        $this->dockerFacade = $dockerFacade;
    }

    public function notify($event)
    {
        if ($event instanceof BuildStepStarted) {
            $step = BuildStep::create(
                $event->getBuildIdentifier(),
                $event->getStepPosition(),
                $event->getStepConfiguration()
            );
        } else {
            $step = $this->notifyExistingStep($event);
        }

        foreach ($step->raisedEvents() as $event) {
            $this->eventBus->handle($event);
        }
    }

    private function notifyExistingStep(StepEvent $event) : BuildStep
    {
        $step = $this->buildStepRepository->find($event->getBuildIdentifier(), $event->getStepPosition());

        if ($event instanceof StepStarted) {
            $step->downloadArchive($this->archiveBuilder);
        } elseif ($event instanceof CodeArchiveCreated) {
            $step->buildImage($this->dockerFacade);
        } elseif ($event instanceof DockerImageBuilt) {
            $step->pushImage($this->dockerFacade);
        }

        return $step;
    }
}
