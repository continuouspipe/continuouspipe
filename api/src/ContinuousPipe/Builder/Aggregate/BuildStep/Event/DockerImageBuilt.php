<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep\Event;

use ContinuousPipe\Builder\Image;

class DockerImageBuilt extends StepEvent
{
    /**
     * @var Image
     */
    private $image;

    public function __construct(string $buildIdentifier, int $stepPosition, Image $image)
    {
        parent::__construct($buildIdentifier, $stepPosition);

        $this->image = $image;
    }

    /**
     * @return Image
     */
    public function getImage(): Image
    {
        return $this->image;
    }
}
