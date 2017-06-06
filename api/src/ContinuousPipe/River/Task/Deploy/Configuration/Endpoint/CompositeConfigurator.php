<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration\Endpoint;

use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\Task\TaskContext;

class CompositeConfigurator implements EndpointConfigurator
{
    /**
     * @var EndpointConfigurator[]
     */
    private $configurations = [];

    /**
     * @param EndpointConfigurator[] $configurations
     */
    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }

    /**
     * @param array $endpointConfiguration
     * @return array
     * @throws TideGenerationException
     */
    public function checkConfiguration(array $endpointConfiguration)
    {
        foreach ($this->configurations as $configuration) {
            $configuration->checkConfiguration($endpointConfiguration);
        }
    }

    /**
     * @param array $endpointConfiguration
     * @param TaskContext $context
     * @return array
     * @throws TideGenerationException
     */
    public function addHost(array $endpointConfiguration, TaskContext $context)
    {
        return array_reduce(
            $this->configurations,
            function (array $config, EndpointConfigurator $configuration) use ($context) {
                return $configuration->addHost($config, $context);
            },
            $endpointConfiguration
        );
    }

}