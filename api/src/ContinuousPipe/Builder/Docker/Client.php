<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Image;
use LogStream\Logger;

interface Client
{
    /**
     * @param Archive $archive
     * @param Image   $image
     * @param Logger  $logger
     *
     * @throws DockerException
     */
    public function build(Archive $archive, Image $image, Logger $logger);

    /**
     * @param Image               $image
     * @param RegistryCredentials $credentials
     * @param Logger              $logger
     *
     * @throws DockerException
     */
    public function push(Image $image, RegistryCredentials $credentials, Logger $logger);
}
