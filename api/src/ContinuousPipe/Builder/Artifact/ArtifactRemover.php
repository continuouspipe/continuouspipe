<?php

namespace ContinuousPipe\Builder\Artifact;

use ContinuousPipe\Builder\Artifact;

interface ArtifactRemover
{
    /**
     * Remove the given artifact.
     *
     * @param Artifact $artifact
     *
     * @throws ArtifactException
     */
    public function remove(Artifact $artifact);
}
