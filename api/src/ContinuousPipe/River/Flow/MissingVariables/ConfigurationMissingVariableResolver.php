<?php

namespace ContinuousPipe\River\Flow\MissingVariables;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\TideConfigurationException;
use ContinuousPipe\River\TideConfigurationFactory;

class ConfigurationMissingVariableResolver implements MissingVariableResolver
{
    /**
     * @var TideConfigurationFactory
     */
    private $tideConfigurationFactory;

    /**
     * @param TideConfigurationFactory $tideConfigurationFactory
     */
    public function __construct(TideConfigurationFactory $tideConfigurationFactory)
    {
        $this->tideConfigurationFactory = $tideConfigurationFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function findMissingVariables(FlatFlow $flow, CodeReference $codeReference) : array
    {
        try {
            $configuration = $this->tideConfigurationFactory->getConfiguration($flow, $codeReference);
        } catch (TideConfigurationException $e) {
            return [];
        }

        $configurationIterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($configuration));
        $missingVariables = [];

        foreach ($configurationIterator as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            foreach ($this->extractVariables($value) as $variable) {
                $missingVariables[] = $variable;
            }
        }

        return array_unique($missingVariables);
    }

    /**
     * @param string $value
     *
     * @return array
     */
    private function extractVariables(string $value) : array
    {
        preg_match_all(
            '#(?<!\\\\)\$\{(?<name>[a-z0-9_-]+)\}#iU',
            $value,
            $matches
        );

        return $matches['name'];
    }
}
