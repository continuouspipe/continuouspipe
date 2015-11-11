<?php

namespace ContinuousPipe\Builder\Docker\HttpClient;

use ContinuousPipe\Builder\Docker\DockerException;

interface OutputHandler
{
    /**
     * @param array|string|null $output
     *
     * @return array|string
     *
     * @throws DockerException
     */
    public function handle($output);
}
