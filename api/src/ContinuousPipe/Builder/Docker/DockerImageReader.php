<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Image;

interface DockerImageReader
{
    /**
     * Read the given path of an image and create an archive from it.
     *
     * @param Image $image
     * @param string $path
     *
     * @throws DockerException
     *
     * @return Archive
     */
    public function read(Image $image, string $path) : Archive;
}
