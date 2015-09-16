<?php

namespace ContinuousPipe\Builder\Tests\Docker;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Docker\Client;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Request\BuildRequest;
use Docker\Container;
use LogStream\Logger;

class EmptyDockerClient implements Client
{
    /**
     * {@inheritdoc}
     */
    public function build(Archive $archive, BuildRequest $request, Logger $logger)
    {
        return $request->getImage();
    }

    /**
     * {@inheritdoc}
     */
    public function push(Image $image, RegistryCredentials $credentials, Logger $logger)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createContainer(Image $image)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function run(Container $container, Logger $logger, $command)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function commit(Container $container, Image $image)
    {
    }
}
