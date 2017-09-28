<?php

namespace ContinuousPipe\River\Task\Build\Configuration;

use ContinuousPipe\Builder\BuildStepConfiguration;
use JMS\Serializer\Annotation as JMS;

class ServiceConfiguration
{
    /**
     * @JMS\Type("array<ContinuousPipe\Builder\BuildStepConfiguration>")
     *
     * @var BuildStepConfiguration[]
     */
    private $builderSteps;

    /**
     * @param BuildStepConfiguration[] $builderSteps
     */
    public function __construct(array $builderSteps)
    {
        $this->builderSteps = $builderSteps;
    }

    /**
     * @return BuildStepConfiguration[]
     */
    public function getBuilderSteps(): array
    {
        return $this->builderSteps ?: [];
    }
}
