<?php

namespace ContinuousPipe\River\Task\Build;

use ContinuousPipe\River\Task\Build\Configuration\ServiceConfiguration;
use JMS\Serializer\Annotation as JMS;

class BuildTaskConfiguration
{
    /**
     * @JMS\Type("array<string, ContinuousPipe\River\Task\Build\Configuration\ServiceConfiguration>")
     *
     * @var ServiceConfiguration[]
     */
    private $services;

    /**
     * @param ServiceConfiguration[] $services
     */
    public function __construct(array $services)
    {
        $this->services = $services;
    }

    /**
     * @return Configuration\ServiceConfiguration[]
     */
    public function getServices()
    {
        return $this->services;
    }

    public function getTideUuid()
    {
    }
}
