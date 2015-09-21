<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Request\BuildRequest;
use Docker\Container;
use LogStream\Logger;

interface Client
{
    /**
     * @param Archive      $archive
     * @param BuildRequest $request
     * @param Logger       $logger
     *
     * @throws DockerException
     *
     * @return Image
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

    /**
     * Create a container from the given image.
     *
     * @param Image $image
     *
     * @return Container
     *
     * @throws DockerException
     */
    public function createContainer(Image $image);

    /**
     * Return the given command in the given container.
     *
     * @param Container $container
     * @param Logger    $logger
     * @param string    $command
     *
     * @return Container
     *
     * @throws DockerException
     */
    public function run(Container $container, Logger $logger, $command);

    /**
     * Commit the given container to the given image.
     *
     * @param Container $container
     * @param Image     $image
     *
     * @return Image
     *
     * @throws DockerException
     */
    public function commit(Container $container, Image $image);
}
