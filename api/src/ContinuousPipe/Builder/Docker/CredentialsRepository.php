<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\User\User;

interface CredentialsRepository
{
    /**
     * Return registry credentials for the given image.
     *
     * @param Image $image
     * @param User  $user
     *
     * @return RegistryCredentials
     */
    public function findByImage(Image $image, User $user);
}
