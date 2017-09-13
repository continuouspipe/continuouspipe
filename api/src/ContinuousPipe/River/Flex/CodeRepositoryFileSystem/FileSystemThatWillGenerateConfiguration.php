<?php

namespace ContinuousPipe\River\Flex\CodeRepositoryFileSystem;

use ContinuousPipe\River\CodeRepository\FileSystem\FileNotFound;
use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use ContinuousPipe\River\Flex\FlowConfigurationGenerator;
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
     * @var FlowConfigurationGenerator
     */
    private $configurationGenerator;

    public function __construct(FlowConfigurationGenerator $configurationGenerator, RelativeFileSystem $decoratedFileSystem, FlatFlow $flow)
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
        try {
            if (null !== ($generated = $this->generateFileIfNeeded($filePath))) {
                return true;
            }
        } catch (FileNotFound $e) {
            // Ignoring file not found exceptions...
        }

        return $this->decoratedFileSystem->exists($filePath);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($filePath)
    {
        if (null !== ($generated = $this->generateFileIfNeeded($filePath))) {
            return $generated;
        }

        return $this->decoratedFileSystem->getContents($filePath);
    }

    /**
     * @param string $filePath
     *
     * @return null|string
     */
    private function generateFileIfNeeded(string $filePath)
    {
        if (!$this->decoratedFileSystem->exists($filePath) && in_array($filePath, self::GENERATED_FILES)) {
            if (null !== ($generated = $this->generateFile($filePath))) {
                return $generated;
            }
        }

        return null;
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

        return null;
    }
}
