<?php

namespace ContinuousPipe\Builder\Image;

use ContinuousPipe\Builder\Image;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

interface Registry
{
    /**
     * @param UuidInterface $credentialsBucket
     * @param Image $image
     *
     * @throws SearchingForExistingImageException
     *
     * @return bool
     */
    public function containsImage(UuidInterface $credentialsBucket, Image $image) : bool;
}
