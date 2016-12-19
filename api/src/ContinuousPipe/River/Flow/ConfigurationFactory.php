<?php

namespace ContinuousPipe\River\Flow;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Task\TaskFactoryRegistry;
use ContinuousPipe\River\TideConfigurationException;
use ContinuousPipe\River\TideConfigurationFactory;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Yaml\Exception\ExceptionInterface as YamlException;
use Symfony\Component\Yaml\Yaml;

class ConfigurationFactory implements TideConfigurationFactory
{
    /**
     * @var FileSystemResolver
     */
    private $fileSystemResolver;

    /**
     * @var TaskFactoryRegistry
     */
    private $taskFactoryRegistry;

    /**
     * @var ConfigurationEnhancer[]
     */
    private $configurationEnhancers;

    /**
     * @var ConfigurationFinalizer[]
     */
    private $configurationFinalizers;

    /**
     * @param FileSystemResolver       $fileSystemResolver
     * @param TaskFactoryRegistry      $taskFactoryRegistry
     * @param ConfigurationEnhancer[]  $configurationEnhancers
     * @param ConfigurationFinalizer[] $configurationFinalizers
     */
    public function __construct(FileSystemResolver $fileSystemResolver, TaskFactoryRegistry $taskFactoryRegistry, array $configurationEnhancers, array $configurationFinalizers)
    {
        $this->fileSystemResolver = $fileSystemResolver;
        $this->taskFactoryRegistry = $taskFactoryRegistry;
        $this->configurationEnhancers = $configurationEnhancers;
        $this->configurationFinalizers = $configurationFinalizers;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(FlatFlow $flow, CodeReference $codeReference)
    {
        try {
            $fileSystem = $this->fileSystemResolver->getFileSystem($flow, $codeReference);
        } catch (CodeRepositoryException $e) {
            throw new TideConfigurationException($e->getMessage(), $e->getCode(), $e);
        }

        $configs = [
            $flow->getConfiguration(),
        ];

        // Read configuration from YML
        if ($fileSystem->exists(self::FILENAME)) {
            try {
                $configs[] = Yaml::parse($fileSystem->getContents(self::FILENAME));
            } catch (YamlException $e) {
                throw new TideConfigurationException(sprintf('Unable to read YAML configuration: %s', $e->getMessage()), $e->getCode(), $e);
            }
        }

        // Enhance configuration
        foreach ($this->configurationEnhancers as $enhancer) {
            $configs = $enhancer->enhance($flow, $codeReference, $configs);
        }

        $configurationDefinition = new Configuration($this->taskFactoryRegistry);

        // Create the normalized configuration
        $configTree = $configurationDefinition->getConfigTreeBuilder()->buildTree();
        $configuration = array();
        foreach ($configs as $config) {
            $config = $configTree->normalize($config);
            $configuration = $configTree->merge($configuration, $config);
        }

        // Enhance this configuration as much as possible
        foreach ($this->configurationFinalizers as $finalizer) {
            $configuration = $finalizer->finalize($flow, $codeReference, $configuration);
        }

        try {
            $configuration = $configTree->finalize($configuration);
        } catch (InvalidConfigurationException $e) {
            throw new TideConfigurationException($e->getMessage(), 0, $e);
        }

        return $configuration;
    }
}
