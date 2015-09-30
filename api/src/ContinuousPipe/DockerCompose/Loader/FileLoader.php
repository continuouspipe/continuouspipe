<?php

namespace ContinuousPipe\DockerCompose\Loader;

use ContinuousPipe\DockerCompose\LocalRelativeFileSystem;
use ContinuousPipe\DockerCompose\Parser\FileParser;
use ContinuousPipe\DockerCompose\Transformer\EnvironmentTransformer;
use ContinuousPipe\Model\Environment;

class FileLoader
{
    /**
     * @var EnvironmentTransformer
     */
    private $environmentTransformer;
    /**
     * @var FileParser
     */
    private $fileParser;

    /**
     * @param FileParser             $fileParser
     * @param EnvironmentTransformer $environmentTransformer
     */
    public function __construct(FileParser $fileParser, EnvironmentTransformer $environmentTransformer)
    {
        $this->environmentTransformer = $environmentTransformer;
        $this->fileParser = $fileParser;
    }

    /**
     * @param string $filePath
     *
     * @return Environment
     */
    public function load($filePath)
    {
        $fileSystem = new LocalRelativeFileSystem();

        $parsed = $this->fileParser->parse($fileSystem, $filePath);

        return $this->environmentTransformer->load(uniqid('project'), $parsed);
    }
}
