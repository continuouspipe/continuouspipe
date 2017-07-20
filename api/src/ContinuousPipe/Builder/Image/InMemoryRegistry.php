<?php

namespace ContinuousPipe\Builder\Image;

use ContinuousPipe\Builder\Image;
use Ramsey\Uuid\Uuid;

class InMemoryRegistry implements Registry
{
    private $images = [];

    public function containsImage(Uuid $credentialsBucket, Image $image): bool
    {
        return in_array(
            sprintf('%s:%s', $image->getName(), $image->getTag()),
            $this->images
        );
    }

    public function addImage($imagePath)
    {
        $this->images[] = $imagePath;
    }
}