<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\CodeArchiveCreated;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\DockerImageBuilt;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\ReadArtifacts;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepEvent;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFailed;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFinished;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepStarted;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\WroteArtifacts;
use ContinuousPipe\Builder\Aggregate\Event\BuildStepStarted;
use ContinuousPipe\Builder\Archive\ArchiveCreationException;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Artifact\ArtifactReader;
use ContinuousPipe\Builder\Artifact\ArtifactWriter;
use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\BuildException;
use ContinuousPipe\Builder\Docker\DockerException;
use ContinuousPipe\Builder\Docker\DockerFacade;
use ContinuousPipe\Builder\Docker\DockerImageReader;
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
    /**
     * @var ArtifactReader
     */
    private $artifactReader;
    /**
     * @var ArtifactWriter
     */
    private $artifactWriter;
    /**
     * @var DockerImageReader
     */
    private $dockerImageReader;

    public function __construct(
        BuildStepRepository $buildStepRepository,
        MessageBus $eventBus,
        ArchiveBuilder $archiveBuilder,
        DockerFacade $dockerFacade,
        ArtifactReader $artifactReader,
        ArtifactWriter $artifactWriter,
        DockerImageReader $dockerImageReader
    ) {
        $this->buildStepRepository = $buildStepRepository;
        $this->eventBus = $eventBus;
        $this->archiveBuilder = $archiveBuilder;
        $this->dockerFacade = $dockerFacade;
        $this->artifactReader = $artifactReader;
        $this->artifactWriter = $artifactWriter;
        $this->dockerImageReader = $dockerImageReader;
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
            $step->readArtifacts($this->artifactReader);
        } elseif ($event instanceof ReadArtifacts) {
            $step->buildImage($this->dockerFacade);
        } elseif ($event instanceof DockerImageBuilt) {
            $step->writeArtifacts($this->dockerImageReader, $this->artifactWriter);
        } elseif ($event instanceof WroteArtifacts) {
            $step->pushImage($this->dockerFacade);
        } elseif ($event instanceof StepFinished || $event instanceof StepFailed) {
            $step->cleanUp();
        }

        return $step;
    }
}
