<?php

namespace ContinuousPipe\River\Task\Build;

use ContinuousPipe\River\Task\Build\Configuration\ServiceConfiguration;
use JMS\Serializer\Annotation as JMS;

class BuildTaskConfiguration
{
    /**
     * @JMS\Type("array<string, string>")
     *
     * @var array
     */
    private $environment;

    /**
     * @JMS\Type("array<string, ContinuousPipe\River\Task\Build\Configuration\ServiceConfiguration>")
     *
     * @var ServiceConfiguration[]
     */
    private $services;

    /**
     * @param array $environment
     * @param array $services
     */
    public function __construct(array $environment, array $services)
    {
        $this->environment = $environment;
        $this->services = $services;
    }

    /**
     * @return array
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return Configuration\ServiceConfiguration[]
     */
    public function getServices()
    {
        return $this->services;
    }
}
