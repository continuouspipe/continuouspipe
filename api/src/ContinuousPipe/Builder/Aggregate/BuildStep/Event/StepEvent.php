<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep\Event;

use ContinuousPipe\Builder\Aggregate\Event\BuildEvent;
use ContinuousPipe\Builder\BuildStepConfiguration;

abstract class StepEvent extends BuildEvent
{
    /**
     * @var int
     */
    private $stepPosition;

    /**
     * @param string $buildIdentifier
     * @param int $stepPosition
     */
    public function __construct(string $buildIdentifier, int $stepPosition)
    {
        parent::__construct($buildIdentifier);

        $this->stepPosition = $stepPosition;
    }

    /**
     * @return int
     */
    public function getStepPosition(): int
    {
        return $this->stepPosition;
    }
}
