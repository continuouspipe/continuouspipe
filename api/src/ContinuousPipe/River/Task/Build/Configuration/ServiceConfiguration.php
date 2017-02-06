<?php

namespace ContinuousPipe\River\Task\Build\Configuration;

use ContinuousPipe\Builder\Request\BuildRequestStep;
use JMS\Serializer\Annotation as JMS;

class ServiceConfiguration
{
    /**
     * @JMS\Type("array<ContinuousPipe\Builder\Request\BuildRequestStep>")
     *
     * @var BuildRequestStep[]
     */
    private $builderSteps;

    /**
     * @param BuildRequestStep[] $builderSteps
     */
    public function __construct(array $builderSteps)
    {
        $this->builderSteps = $builderSteps;
    }

    /**
     * @return BuildRequestStep[]
     */
    public function getBuilderSteps(): array
    {
        return $this->builderSteps ?: [];
    }
}
