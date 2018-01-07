<?php

namespace ContinuousPipe\DockerCompose\Loader;

use ContinuousPipe\DockerCompose\Parser\ProjectParser;
use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use ContinuousPipe\DockerCompose\Transformer\EnvironmentTransformer;
use ContinuousPipe\Model\Environment;

class ProjectLoader
{
    /**
     * @var EnvironmentTransformer
     */
    private $environmentTransformer;

    /**
     * @var ProjectParser
     */
    private $projectParser;

    /**
     * @param ProjectParser          $projectParser
     * @param EnvironmentTransformer $environmentTransformer
     */
    public function __construct(ProjectParser $projectParser, EnvironmentTransformer $environmentTransformer)
    {
        $this->projectParser = $projectParser;
        $this->environmentTransformer = $environmentTransformer;
    }

    /**
     * @param \ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem $fileSystem
     * @param string             $environment
     *
     * @return Environment
     */
    public function load(RelativeFileSystem $fileSystem, $environment = null)
    {
        $parsed = $this->projectParser->parse($fileSystem, $environment);

        return $this->environmentTransformer->load(uniqid('env-'), $parsed);
    }
}
