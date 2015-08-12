<?php

namespace ContinuousPipe\River\Task\Deploy\DockerCompose;

use ContinuousPipe\River\Task\Deploy\DeployContext;

interface DockerComposeTransformer
{
    /**
     * Transform the parsed Docker-Compose contents.
     *
     * @param DeployContext $context
     * @param array $parsed
     * @return array
     */
    public function transform(DeployContext $context, array $parsed);
}
