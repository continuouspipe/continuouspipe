<?php

namespace ContinuousPipe\Builder\Artifact;

use ContinuousPipe\Builder\Artifact;

class TracedArtifactRemover implements ArtifactRemover
{
    /**
     * @var ArtifactRemover
     */
    private $decoratedRemover;

    /**
     * @var Artifact[]
     */
    private $removed = [];

    public function __construct(ArtifactRemover $decoratedRemover)
    {
        $this->decoratedRemover = $decoratedRemover;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Artifact $artifact)
    {
        $this->decoratedRemover->remove($artifact);

        $this->removed[] = $artifact;
    }

    /**
     * @return Artifact[]
     */
    public function getRemoved(): array
    {
        return $this->removed;
    }
}
