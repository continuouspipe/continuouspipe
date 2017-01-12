<?php

namespace ContinuousPipe\Builder\Aggregate\Event;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;

class BuildStepStarted extends BuildEvent
{
    /**
     * @var int
     */
    private $stepPosition;

    /**
     * @var BuildStepConfiguration
     */
    private $stepConfiguration;

    public function __construct(string $buildIdentifier, int $stepPosition, BuildStepConfiguration $stepConfiguration)
    {
        parent::__construct($buildIdentifier);

        $this->stepPosition = $stepPosition;
        $this->stepConfiguration = $stepConfiguration;
    }

    /**
     * @return int
     */
    public function getStepPosition(): int
    {
        return $this->stepPosition;
    }

    /**
     * @return BuildStepConfiguration
     */
    public function getStepConfiguration(): BuildStepConfiguration
    {
        return $this->stepConfiguration;
    }
}
