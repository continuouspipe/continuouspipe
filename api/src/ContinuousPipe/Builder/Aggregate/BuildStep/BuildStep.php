<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep;

use ContinuousPipe\Builder\Aggregate\BuildStep\Event\CodeArchiveCreated;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\DockerImageBuilt;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\ReadArtifacts;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFailed;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFinished;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\WroteArtifacts;
use ContinuousPipe\Builder\Aggregate\Event\BuildFinished;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepStarted;
use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Artifact\ArtifactException;
use ContinuousPipe\Builder\Artifact\ArtifactReader;
use ContinuousPipe\Builder\Artifact\ArtifactWriter;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Docker\BuildContext;
use ContinuousPipe\Builder\Docker\CredentialsRepository;
use ContinuousPipe\Builder\Docker\DockerException;
use ContinuousPipe\Builder\Docker\DockerFacade;
use ContinuousPipe\Builder\Docker\PushContext;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Events\Capabilities\ApplyEventCapability;
use ContinuousPipe\Events\Capabilities\RaiseEventCapability;
use ContinuousPipe\Security\Credentials\BucketRepository;

class BuildStep
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_FAILED = 'failed';
    const STATUS_SUCCESSFUL = 'successful';

    use RaiseEventCapability,
        ApplyEventCapability;

    /**
     * @var int
     */
    private $position;

    /**
     * @var BuildStepConfiguration
     */
    private $configuration;

    /**
     * @var string
     */
    private $buildIdentifier;

    /**
     * @var Archive
     */
    private $archive;

    /**
     * @var Image
     */
    private $image;

    /**
     * @var string
     */
    private $status = self::STATUS_PENDING;

    private function __construct()
    {
    }

    public static function create(string $buildIdentifier, int $position, BuildStepConfiguration $configuration)
    {
        $build = new self();
        $build->raise(new StepStarted(
            $buildIdentifier,
            $position,
            $configuration
        ));

        return $build;
    }

    public function downloadArchive(ArchiveBuilder $archiveBuilder)
    {
        try {
            $this->raise(new CodeArchiveCreated(
                $this->buildIdentifier,
                $this->position,
                $archiveBuilder->createArchive($this->configuration)
            ));
        } catch (Archive\ArchiveCreationException $e) {
            $this->failed($e);
        }
    }

    public function buildImage(DockerFacade $dockerFacade)
    {
        $image = $this->configuration->getImage();

        // We want to build an image but not to push it apparently
        if (null === $image) {
            $image = new Image($this->buildIdentifier, 'step-'.$this->position);
        }

        try {
            $this->raise(new DockerImageBuilt(
                $this->buildIdentifier,
                $this->position,
                $dockerFacade->build(
                    new BuildContext(
                        $this->configuration->getLogStreamIdentifier(),
                        $this->configuration->getContext(),
                        $this->configuration->getEnvironment(),
                        $this->configuration->getDockerRegistries(),
                        $image
                    ),
                    $this->archive
                )
            ));
        } catch (DockerException $e) {
            $this->failed($e);
        }
    }

    public function pushImage(DockerFacade $dockerFacade)
    {
        // We don't want to push the built image
        if (null === $this->configuration->getImage()) {
            $this->finish();
            return;
        }

        try {
            $dockerFacade->push(
                new PushContext(
                    $this->configuration->getLogStreamIdentifier(),
                    $this->configuration->getImageRegistryCredentials()
                ),
                $this->image
            );

            $this->finish();
        } catch (DockerException $e) {
            $this->failed($e);
        }
    }

    private function finish()
    {
        $this->raise(new StepFinished(
            $this->buildIdentifier,
            $this->position
        ));
    }

    private function failed(\Throwable $exception)
    {
        $this->raise(new StepFailed(
            $this->buildIdentifier,
            $this->position,
            $exception,
            $this->configuration->getLogStreamIdentifier()
        ));
    }

    public function readArtifacts(ArtifactReader $artifactReader)
    {
        foreach ($this->configuration->getReadArtifacts() as $artifact) {
            try {
                $artifactReader->read($artifact, $this->archive);
            } catch (ArtifactException $e) {
                $this->failed($e);
            }
        }

        $this->raise(new ReadArtifacts(
            $this->buildIdentifier,
            $this->position
        ));
    }

    public function writeArtifacts(ArtifactWriter $artifactWriter)
    {
        foreach ($this->configuration->getWriteArtifacts() as $artifact) {
            try {
                $artifactWriter->write($this->image, $artifact);
            } catch (ArtifactException $e) {
                $this->failed($e);
            }
        }

        $this->raise(new WroteArtifacts(
            $this->buildIdentifier,
            $this->position
        ));
    }

    public function applyStepStarted(StepStarted $event)
    {
        $this->status = self::STATUS_RUNNING;
        $this->buildIdentifier = $event->getBuildIdentifier();
        $this->position = $event->getStepPosition();
        $this->configuration = $event->getStepConfiguration();
    }

    private function applyReadArtifacts()
    {
    }

    private function applyWroteArtifacts()
    {
    }

    private function applyStepFailed()
    {
        $this->status = self::STATUS_FAILED;
    }

    private function applyStepFinish()
    {
        $this->status = self::STATUS_SUCCESSFUL;
    }

    private function applyDockerImageBuilt(DockerImageBuilt $event)
    {
        $this->image = $event->getImage();
    }

    private function applyCodeArchiveCreated(CodeArchiveCreated $event)
    {
        $this->archive = $event->getArchive();
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
