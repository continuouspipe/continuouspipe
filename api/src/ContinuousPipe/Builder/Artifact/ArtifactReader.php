<?php

namespace ContinuousPipe\Builder\Artifact;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Artifact;

interface ArtifactReader
{
    /**
     * @param Artifact $artifact
     * @param Archive $into
     *
     * @throws ArtifactException
     *
     * @return Archive
     */
    public function read(Artifact $artifact, Archive $into) : Archive;
}
