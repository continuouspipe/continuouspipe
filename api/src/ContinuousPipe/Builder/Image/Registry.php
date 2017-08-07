<?php

namespace ContinuousPipe\Builder\Image;

use ContinuousPipe\Builder\Image;
use Ramsey\Uuid\Uuid;

interface Registry
{
    public function containsImage(Uuid $credentialsBucket, Image $image) : bool;
}