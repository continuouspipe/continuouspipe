<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep;

use ContinuousPipe\Builder\Aggregate\BuildStep\Event\CodeArchiveCreated;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\DockerImageBuilt;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFailed;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFinished;
use ContinuousPipe\Builder\Aggregate\Event\BuildFinished;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepStarted;
use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\ArchiveBuilder;
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
                        $this->configuration->getImage()
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
        try {
            $dockerFacade->push(
                new PushContext(
                    $this->configuration->getLogStreamIdentifier(),
                    $this->configuration->getImageRegistryCredentials()
                ),
                $this->image
            );

            $this->raise(new StepFinished(
                $this->buildIdentifier,
                $this->position
            ));
        } catch (DockerException $e) {
            $this->failed($e);
        }
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

    public function applyStepStarted(StepStarted $event)
    {
        $this->buildIdentifier = $event->getBuildIdentifier();
        $this->position = $event->getStepPosition();
        $this->configuration = $event->getStepConfiguration();
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
     * @return Archive
     */
    public function getArchive(): Archive
    {
        return $this->archive;
    }

    /**
     * @return Image
     */
    public function getImage(): Image
    {
        return $this->image;
    }
}
