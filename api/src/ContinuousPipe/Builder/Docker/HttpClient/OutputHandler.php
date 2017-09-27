<?php

namespace ContinuousPipe\Builder\Docker\HttpClient;

use ContinuousPipe\Builder\Docker\DockerException;
use Docker\API\Model\BuildInfo;
use Docker\API\Model\CreateImageInfo;

interface OutputHandler
{
    /**
     * @param BuildInfo|CreateImageInfo|array|string|null $output
     *
     * @return array|string
     *
     * @throws DockerException
     */
    public function handle($output);
}
