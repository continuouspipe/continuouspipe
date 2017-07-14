<?php

namespace ContinuousPipe\River\Flex\CodeRepositoryFileSystem;

use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\DockerCompose\RelativeFileSystem;
use ContinuousPipe\River\Flex\ConfigurationGenerator;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class FileSystemThatWillGenerateConfiguration implements RelativeFileSystem
{
    const GENERATED_FILES = [
        'continuous-pipe.yml',
        'docker-compose.yml',
        'Dockerfile'
    ];

    /**
     * @var RelativeFileSystem
     */
    private $decoratedFileSystem;

    /**
     * @var FlatFlow
     */
    private $flow;

    /**
     * @var ConfigurationGenerator
     */
    private $configurationGenerator;

    public function __construct(RelativeFileSystem $decoratedFileSystem, FlatFlow $flow)
    {
        $this->decoratedFileSystem = $decoratedFileSystem;
        $this->flow = $flow;
        $this->configurationGenerator = new ConfigurationGenerator();
    }

    /**
     * {@inheritdoc}
     */
    public function exists($filePath)
    {
        if (in_array($filePath, self::GENERATED_FILES)) {
            return true;
        }

        return $this->decoratedFileSystem->exists($filePath);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($filePath)
    {
        if (in_array($filePath, self::GENERATED_FILES)) {
            return $this->generateFile($filePath);
        }

        return $this->decoratedFileSystem->getContents($filePath);
    }

    private function generateFile(string $filePath)
    {
        $generatedFiles = $this->configurationGenerator->generate($this->decoratedFileSystem, $this->flow);

        if (!array_key_exists($filePath, $generatedFiles)) {
            throw new FileNotFound('File '.$filePath.' was not generated');
        }

        return $generatedFiles[$filePath];
    }
}
