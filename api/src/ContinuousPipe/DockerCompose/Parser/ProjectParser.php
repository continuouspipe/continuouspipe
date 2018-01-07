<?php

namespace ContinuousPipe\DockerCompose\Parser;

use ContinuousPipe\DockerCompose\DockerComposeException;
use ContinuousPipe\River\CodeRepository\FileSystem\FileException;
use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;

interface ProjectParser
{
    /**
     * Load project environment from the configuration files found.
     *
     * The environment variable make us able to use another configuration file, basically constructed as
     * `docker-compose.[environment-name].yml`. This file will be loaded in top of the default one.
     *
     * @param RelativeFileSystem $fileSystem
     * @param string             $environment
     *
     * @throws DockerComposeException
     * @throws FileException
     *
     * @return array
     */
    public function parse(RelativeFileSystem $fileSystem, $environment = null);
}
