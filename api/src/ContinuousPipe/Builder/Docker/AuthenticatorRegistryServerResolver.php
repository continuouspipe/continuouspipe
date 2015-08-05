<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Image;

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

        return 'default';
    }
}
