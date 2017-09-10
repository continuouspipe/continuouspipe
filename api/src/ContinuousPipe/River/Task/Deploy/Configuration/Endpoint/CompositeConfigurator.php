<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration\Endpoint;

use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\Task\TaskContext;

class CompositeConfigurator implements EndpointConfigurationEnhancer
{
    /**
     * @var EndpointConfigurationEnhancer[]
     */
    private $enhancers = [];

    /**
     * @param EndpointConfigurationEnhancer[] $configurations
     */
    public function __construct(array $configurations)
    {
        $this->enhancers = $configurations;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(array $endpointConfiguration, TaskContext $context)
    {
        return array_reduce($this->enhancers, function (array $config, EndpointConfigurationEnhancer $configuration) use ($context) {
            return $configuration->enhance($config, $context);
        }, $endpointConfiguration);
    }
}
