<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Context;

class DockerfileResolver
{
    const DEFAULT_DOCKER_FILE_PATH = './Dockerfile';

    /**
     * @param Context $context
     *
     * @return string
     */
    public function getFilePath(Context $context = null)
    {
        $dockerFilePath = null !== $context ? $context->getDockerFilePath() : null;

        if (empty($dockerFilePath)) {
            $dockerFilePath = self::DEFAULT_DOCKER_FILE_PATH;
        }

        return $dockerFilePath;
    }
}
