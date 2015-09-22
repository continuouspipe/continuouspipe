<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\Flow\Configuration;
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
     * @param FileSystemResolver  $fileSystemResolver
     * @param TaskFactoryRegistry $taskFactoryRegistry
     */
    public function __construct(FileSystemResolver $fileSystemResolver, TaskFactoryRegistry $taskFactoryRegistry)
    {
        $this->fileSystemResolver = $fileSystemResolver;
        $this->taskFactoryRegistry = $taskFactoryRegistry;
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
        $configs = [
            $flow->getContext()->getConfiguration(),
        ];

        // Read configuration from YML
        $fileSystem = $this->fileSystemResolver->getFileSystem($codeReference, $flow->getContext()->getUser());
        if ($fileSystem->exists(self::FILENAME)) {
            $configs[] = Yaml::parse($fileSystem->getContents(self::FILENAME));
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
     * @return array
     */
    private function replaceVariables(array $configuration)
    {
        $variables = $this->resolveVariables($configuration);
        $variableKeys = array_map(function($key) {
            return sprintf('${%s}', $key);
        }, array_keys($variables));

        array_walk_recursive($configuration, function(&$value) use ($variableKeys, $variables) {
            if (is_string($value)) {
                $value = str_replace($variableKeys, array_values($variables), $value);
            }
        });

        return $configuration;
    }

    /**
     * @param array $configuration
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
