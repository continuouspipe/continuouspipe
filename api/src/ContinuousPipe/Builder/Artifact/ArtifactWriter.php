<?php

namespace ContinuousPipe\Builder\Artifact;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Artifact;

interface ArtifactWriter
{
    /**
     * Create the artifact from the given image.
     *
     * @param Archive  $source
     * @param Artifact $artifact
     * @param string   $format
     *
     * @throws ArtifactException
     */
    public function write(Archive $source, Artifact $artifact, string $format = null);
}
