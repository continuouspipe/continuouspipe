<?php

namespace ContinuousPipe\River\Flow\MissingVariables;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Pipe\DeploymentRequest\DynamicVariable\EndpointVariable;
use ContinuousPipe\River\Pipe\DeploymentRequest\DynamicVariable\ServiceVariable;
use ContinuousPipe\River\TideConfigurationException;
use ContinuousPipe\River\TideConfigurationFactory;
use Psr\Log\LoggerInterface;

class ConfigurationMissingVariableResolver implements MissingVariableResolver
{
    /**
     * @var TideConfigurationFactory
     */
    private $tideConfigurationFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(TideConfigurationFactory $tideConfigurationFactory, LoggerInterface $logger)
    {
        $this->tideConfigurationFactory = $tideConfigurationFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function findMissingVariables(FlatFlow $flow, CodeReference $codeReference) : array
    {
        try {
            $configuration = $this->tideConfigurationFactory->getConfiguration($flow, $codeReference, false);
        } catch (TideConfigurationException $e) {
            throw $e;
            $this->logger->warning('Unable to find missing variables because of the tide configuration', [
                'exception' => $e,
            ]);

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

        $missingVariables = array_filter($missingVariables, function ($variable) {
            return !ServiceVariable::isValidVariableName($variable)
                && !EndpointVariable::isValidVariableName($variable);
        });

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
