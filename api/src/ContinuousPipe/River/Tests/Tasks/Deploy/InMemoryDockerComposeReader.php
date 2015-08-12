<?php

namespace ContinuousPipe\River\Tests\Tasks\Deploy;

use ContinuousPipe\River\Task\Deploy\DeployContext;
use ContinuousPipe\River\Task\Deploy\DockerCompose\DockerComposeReader;

class InMemoryDockerComposeReader implements DockerComposeReader
{
    /**
     * {@inheritdoc}
     */
    public function getContents(DeployContext $context)
    {
        return '';
    }
}
