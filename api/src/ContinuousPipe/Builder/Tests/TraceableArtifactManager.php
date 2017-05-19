<?php

namespace ContinuousPipe\Builder\Tests;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Artifact\ArtifactManager;

class TraceableArtifactManager implements ArtifactManager
{

    private $written = [];

    public function read(Artifact $artifact) : Archive
    {
        // TODO: Implement read() method.
    }

    public function remove(Artifact $artifact)
    {
        // TODO: Implement remove() method.
    }

    public function write(Archive $source, Artifact $artifact, string $format = null)
    {
        $this->written[] = [
            'archive' => $source,
            'artifact' => $artifact,
            'format' => $format,
        ];
    }

    public function getWritten()
    {
        return $this->written;
    }
    
}
