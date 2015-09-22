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
        // Read configuration from YML
        $fileSystem = $this->fileSystemResolver->getFileSystem($codeReference, $flow->getContext()->getUser());
        if (!$fileSystem->exists(self::FILENAME)) {
            throw new TideConfigurationException(sprintf(
                'The configuration file "%s" do not exists in code repository',
                self::FILENAME
            ));
        }

        $configs = [
            Yaml::parse($fileSystem->getContents(self::FILENAME)),
        ];

        $configuration = new Configuration($this->taskFactoryRegistry);
        $processor = new Processor();

        try {
            return $processor->processConfiguration($configuration, $configs);
        } catch (InvalidConfigurationException $e) {
            throw new TideConfigurationException($e->getMessage(), 0, $e);
        }
    }
}
