<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Logger;

interface Client
{
    /**
     * @param Archive      $archive
     * @param BuildRequest $request
     * @param Logger       $logger
     *
     * @throws DockerException
     */
    public function build(Archive $archive, BuildRequest $request, Logger $logger);

    /**
     * @param Image               $image
     * @param RegistryCredentials $credentials
     * @param Logger              $logger
     *
     * @throws DockerException
     */
    public function push(Image $image, RegistryCredentials $credentials, Logger $logger);
}
