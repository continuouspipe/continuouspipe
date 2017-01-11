<?php

namespace ContinuousPipe\Builder\Aggregate;

use ContinuousPipe\Builder\Aggregate\BuildStep\Event\DockerImageBuilt;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFailed;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFinished;
use ContinuousPipe\Builder\Aggregate\Event\BuildFinished;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepStarted;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Events\Capabilities\ApplyEventCapability;
use ContinuousPipe\Events\Capabilities\RaiseEventCapability;

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

    public function __construct(string $buildIdentifier, int $position, BuildStepConfiguration $configuration)
    {
        $this->position = $position;
        $this->configuration = $configuration;
        $this->buildIdentifier = $buildIdentifier;
    }

    public function start()
    {
        $this->raise(new StepStarted(
            $this->buildIdentifier,
            $this->position,
            $this->configuration
        ));
    }

    public function buildFinished(\Throwable $exception = null)
    {
        if (null !== $exception) {
            $this->raise(new StepFailed(
                $this->buildIdentifier,
                $this->position
            ));
        } else {
            $this->raise(new DockerImageBuilt(
                $this->buildIdentifier,
                $this->position
            ));
        }
    }

    public function pushFinished(\Throwable $exception = null)
    {
        if (null !== $exception) {
            $this->raise(new StepFailed(
                $this->buildIdentifier,
                $this->position
            ));
        } else {
            $this->raise(new StepFinished(
                $this->buildIdentifier,
                $this->position
            ));
        }
    }
}
