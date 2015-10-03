<?php

namespace ContinuousPipe\River\Flow\ConfigurationFinalizer;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Flow\ConfigurationFinalizer;
use ContinuousPipe\River\TideConfigurationFactory;

class ReplaceEnvironmentVariableValues implements TideConfigurationFactory
{
    /**
     * @var TideConfigurationFactory
     */
    private $factory;

    /**
     * @param TideConfigurationFactory $factory
     */
    public function __construct(TideConfigurationFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(Flow $flow, CodeReference $codeReference)
    {
        $configuration = $this->factory->getConfiguration($flow, $codeReference);

        $variables = $this->resolveVariables($configuration);
        $variableKeys = array_map(function ($key) {
            return sprintf('${%s}', $key);
        }, array_keys($variables));

        array_walk_recursive($configuration, function (&$value) use ($variableKeys, $variables) {
            if (is_string($value)) {
                $value = str_replace($variableKeys, array_values($variables), $value);
            }
        });

        return $configuration;
    }

    /**
     * @param array $configuration
     *
     * @return array
     */
    private function resolveVariables(array $configuration)
    {
        $variables = [];
        foreach ($configuration['environment_variables'] as $item) {
            $variables[$item['name']] = $item['value'];
        }

        return $variables;
    }
}
