<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Image;
use ContinuousPipe\User\DockerRegistryCredentials;

class AuthenticatorRegistryServerResolver implements RegistryServerResolver
{
    /**
     * {@inheritdoc}
     */
    public function getServerName(Image $image)
    {
        $parts = explode('/', $image->getName());

        if (strpos($parts[0], '.') !== false) {
            return $parts[0];
        }

        return 'docker.io';
    }
}
