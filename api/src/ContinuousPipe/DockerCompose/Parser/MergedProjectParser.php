<?php

namespace ContinuousPipe\DockerCompose\Parser;

use ContinuousPipe\DockerCompose\RelativeFileSystem;

class MergedProjectParser implements ProjectParser
{
    const DOCKER_COMPOSE_FILE = 'docker-compose.yml';

    /**
     * @var FileParser
     */
    private $fileParser;

    /**
     * @param FileParser $fileParser
     */
    public function __construct(FileParser $fileParser)
    {
        $this->fileParser = $fileParser;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(RelativeFileSystem $fileSystem, $environment = null)
    {
        $parsed = $this->fileParser->parse($fileSystem, self::DOCKER_COMPOSE_FILE);

        if ($environment !== null) {
            $environmentFile = substr(self::DOCKER_COMPOSE_FILE, 0, -3).$environment.'.yml';

            if ($fileSystem->exists($environmentFile)) {
                $parsed = array_replace_recursive($parsed, $this->fileParser->parse($fileSystem, $environmentFile));
            }
        }

        return $parsed;
    }
}
