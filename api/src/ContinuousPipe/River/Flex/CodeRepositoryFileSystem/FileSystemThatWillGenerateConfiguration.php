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

    public function __construct(ConfigurationGenerator $configurationGenerator, RelativeFileSystem $decoratedFileSystem, FlatFlow $flow)
    {
        $this->configurationGenerator = $configurationGenerator;
        $this->decoratedFileSystem = $decoratedFileSystem;
        $this->flow = $flow;
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
        $generatedConfiguration = $this->configurationGenerator->generate($this->decoratedFileSystem, $this->flow);

        foreach ($generatedConfiguration->getGeneratedFiles() as $generatedFile) {
            if ($generatedFile->getPath() == $filePath) {
                if ($generatedFile->hasFailed()) {
                    throw new FileNotFound('Generation of file '.$filePath.' failed: '.$generatedFile->getFailureReason());
                }

                return $generatedFile->getContents();
            }
        }

        throw new FileNotFound('File '.$filePath.' was not generated');
    }
}
