<?php

namespace ContinuousPipe\Builder\Image;

use ContinuousPipe\Builder\Image;
use Ramsey\Uuid\Uuid;

interface Registry
{
    /**
     * @param Uuid $credentialsBucket
     * @param Image $image
     *
     * @throws SearchingForExistingImageException
     *
     * @return bool
     */
    public function containsImage(Uuid $credentialsBucket, Image $image) : bool;
}
