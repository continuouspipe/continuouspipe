<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Security\Authenticator\CredentialsNotFound;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

interface CredentialsRepository
{
    /**
     * Return registry credentials for the given image.
     *
     * @param Image $image
     * @param UuidInterface $credentialsBucketUuid
     *
     * @throws CredentialsNotFound
     *
     * @return RegistryCredentials
     */
    public function findByImage(Image $image, UuidInterface $credentialsBucketUuid);

    /**
     * @throws CredentialsNotFound
     */
    public function findRegistryByImage(Image $image, UuidInterface $credentialsBucketUuid): DockerRegistry;
}
