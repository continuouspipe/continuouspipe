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
     * @var Flow\ConfigurationFinalizer[]
     */
    private $configurationFinalizers;

    /**
     * @param FileSystemResolver      $fileSystemResolver
     * @param TaskFactoryRegistry     $taskFactoryRegistry
     * @param ConfigurationEnhancer[] $configurationEnhancers
     * @param array                   $configurationFinalizers
     */
    public function __construct(FileSystemResolver $fileSystemResolver, TaskFactoryRegistry $taskFactoryRegistry, array $configurationEnhancers, array $configurationFinalizers)
    {
        $this->fileSystemResolver = $fileSystemResolver;
        $this->taskFactoryRegistry = $taskFactoryRegistry;
        $this->configurationEnhancers = $configurationEnhancers;
        $this->configurationFinalizers = $configurationFinalizers;
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

        foreach ($this->configurationFinalizers as $finalizer) {
            $configuration = $finalizer->finalize($configuration);
        }

        return $configuration;
    }
}
