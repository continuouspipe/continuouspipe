<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Image;

interface RegistryServerResolver
{
    /**
     * Get name of the Docker Registry server for the given image.
     *
     * @param Image $image
     *
     * @return string
     */
    public function getServerName(Image $image);
}
