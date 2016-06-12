<?php

namespace ContinuousPipe\DockerCompose\Parser;

use ContinuousPipe\DockerCompose\DockerComposeException;
use ContinuousPipe\DockerCompose\RelativeFileSystem;

class V2CompatibilityParser implements ProjectParser
{
    /**
     * @var ProjectParser
     */
    private $decoratedParser;

    /**
     * @param ProjectParser $decoratedParser
     */
    public function __construct(ProjectParser $decoratedParser)
    {
        $this->decoratedParser = $decoratedParser;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(RelativeFileSystem $fileSystem, $environment = null)
    {
        $parsed = $this->decoratedParser->parse($fileSystem, $environment);

        if (!array_key_exists('version', $parsed) || 1 === ($version = (int) $parsed['version'])) {
            return $parsed;
        }

        if (!array_key_exists('services', $parsed)) {
            throw new DockerComposeException('The Docker Compose file is invalid');
        }

        return $parsed['services'];
    }
}
