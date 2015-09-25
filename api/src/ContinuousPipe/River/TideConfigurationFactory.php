<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\Flow\Configuration;
use ContinuousPipe\River\Flow\ConfigurationEnhancer;
use ContinuousPipe\River\Task\TaskFactoryRegistry;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class TideConfigurationFactory
{
    const FILENAME = 'continuous-pipe.yml';

    /**
     * @var FileSystemResolver
     */
    private $fileSystemResolver;

    /**
     * @var TaskFactoryRegistry
     */
    private $taskFactoryRegistry;

    /**
     * @var Flow\ConfigurationEnhancer[]
     */
    private $configurationEnhancers;

    /**
     * @param FileSystemResolver      $fileSystemResolver
     * @param TaskFactoryRegistry     $taskFactoryRegistry
     * @param ConfigurationEnhancer[] $configurationEnhancers
     */
    public function __construct(FileSystemResolver $fileSystemResolver, TaskFactoryRegistry $taskFactoryRegistry, array $configurationEnhancers)
    {
        $this->fileSystemResolver = $fileSystemResolver;
        $this->taskFactoryRegistry = $taskFactoryRegistry;
        $this->configurationEnhancers = $configurationEnhancers;
    }

    /**
     * @param Flow          $flow
     * @param CodeReference $codeReference
     *
     * @return array
     *
     * @throws TideConfigurationException
     */
    public function getConfiguration(Flow $flow, CodeReference $codeReference)
    {
        $flowContext = $flow->getContext();
        $fileSystem = $this->fileSystemResolver->getFileSystem($codeReference, $flowContext->getUser());

        $configs = [
            $flowContext->getConfiguration(),
        ];

        // Read configuration from YML
        if ($fileSystem->exists(self::FILENAME)) {
            $configs[] = Yaml::parse($fileSystem->getContents(self::FILENAME));
        }

        // Enhance configuration
        foreach ($this->configurationEnhancers as $enhancer) {
            $configs = $enhancer->enhance($flow, $codeReference, $configs);
        }

        $configurationDefinition = new Configuration($this->taskFactoryRegistry);
        $processor = new Processor();

        try {
            $configuration = $processor->processConfiguration($configurationDefinition, $configs);
        } catch (InvalidConfigurationException $e) {
            throw new TideConfigurationException($e->getMessage(), 0, $e);
        }

        return $this->replaceVariables($configuration);
    }

    /**
     * @param array $configuration
     *
     * @return array
     */
    private function replaceVariables(array $configuration)
    {
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
