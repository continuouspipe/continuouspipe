<?php

namespace ContinuousPipe\River\Task\Deploy\DockerCompose;

use ContinuousPipe\River\Task\Deploy\DeployContext;

interface DockerComposeReader
{
    /**
     * Read the contents of the docker-compose file for the given deploy context.
     *
     * @param DeployContext $context
     *
     * @return string
     */
    public function getContents(DeployContext $context);
}
