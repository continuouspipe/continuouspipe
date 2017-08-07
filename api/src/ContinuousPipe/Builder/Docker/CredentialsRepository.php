<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Security\Authenticator\CredentialsNotFound;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use Ramsey\Uuid\Uuid;

interface CredentialsRepository
{
    /**
     * Return registry credentials for the given image.
     *
     * @param Image $image
     * @param Uuid  $credentialsBucketUuid
     *
     * @throws CredentialsNotFound
     *
     * @return RegistryCredentials
     */
    public function findByImage(Image $image, Uuid $credentialsBucketUuid);

    /**
     * @throws CredentialsNotFound
     */
    public function findRegistryByImage(Image $image, Uuid $credentialsBucketUuid): DockerRegistry;
}
