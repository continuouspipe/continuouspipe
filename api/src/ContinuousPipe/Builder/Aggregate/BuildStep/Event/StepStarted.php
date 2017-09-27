<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep\Event;

use ContinuousPipe\Builder\BuildStepConfiguration;

class StepStarted extends StepEvent
{
    /**
     * @var BuildStepConfiguration
     */
    private $stepConfiguration;

    /**
     * @param string $buildIdentifier
     * @param int $stepPosition
     * @param BuildStepConfiguration $stepConfiguration
     */
    public function __construct(string $buildIdentifier, int $stepPosition, BuildStepConfiguration $stepConfiguration)
    {
        parent::__construct($buildIdentifier, $stepPosition);

        $this->stepConfiguration = $stepConfiguration;
    }

    /**
     * @return BuildStepConfiguration
     */
    public function getStepConfiguration(): BuildStepConfiguration
    {
        return $this->stepConfiguration;
    }
}
