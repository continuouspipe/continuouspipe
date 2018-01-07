<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep\Event;

use ContinuousPipe\Builder\Aggregate\Event\BuildEvent;
use ContinuousPipe\Builder\BuildStepConfiguration;

abstract class StepEvent
{
    /**
     * @var int
     */
    private $stepPosition;
    /**
     * @var string
     */
    private $buildIdentifier;

    /**
     * @param string $buildIdentifier
     * @param int $stepPosition
     */
    public function __construct(string $buildIdentifier, int $stepPosition)
    {
        $this->stepPosition = $stepPosition;
        $this->buildIdentifier = $buildIdentifier;
    }

    /**
     * @return int
     */
    public function getStepPosition(): int
    {
        return $this->stepPosition;
    }

    /**
     * @return string
     */
    public function getBuildIdentifier(): string
    {
        return $this->buildIdentifier;
    }
}
