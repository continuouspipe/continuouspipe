<?php

namespace ContinuousPipe\River;

use Builder\Image;
use ContinuousPipe\Model\Environment;

interface EnvironmentResolver
{
    /**
     * Get the environment related to that given image.
     *
     * @param Image $image
     *
     * @return Environment
     */
    public function getEnvironmentForImage(Image $image);
}
