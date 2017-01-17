<?php

namespace ContinuousPipe\Builder\Artifact;

use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Image;

interface ArtifactWriter
{
    /**
     * Create the artifact from the given image.
     *
     * @param Image $source
     * @param Artifact $artifact
     *
     * @throws ArtifactException
     */
    public function write(Image $source, Artifact $artifact);
}
