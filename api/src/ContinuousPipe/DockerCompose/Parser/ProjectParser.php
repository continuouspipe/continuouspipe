<?php

namespace ContinuousPipe\DockerCompose\Parser;

use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\DockerCompose\RelativeFileSystem;

class ProjectParser
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
     * Load project environment from the configuration files found.
     *
     * The environment variable make us able to use another configuration file, basically constructed as
     * `docker-compose.[environment-name].yml`. This file will be loaded in top of the default one.
     *
     * @param RelativeFileSystem $fileSystem
     * @param string             $environment
     *
     * @throws FileNotFound
     *
     * @return array
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
