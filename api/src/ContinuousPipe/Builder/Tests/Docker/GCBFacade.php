<?php

namespace ContinuousPipe\Builder\Tests\Docker;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Docker\BuildContext;
use ContinuousPipe\Builder\Docker\DockerException;
use ContinuousPipe\Builder\Docker\DockerFacade;
use ContinuousPipe\Builder\Docker\PushContext;
use ContinuousPipe\Builder\Image;

class GCBFacade implements DockerFacade
{

    /**
     * @param BuildContext $context
     * @param Archive $archive
     *
     * @throws DockerException
     *
     * @return Image
     */
    public function build(BuildContext $context, Archive $archive) : Image
    {
        return $context->getImage();
    }

    /**
     * @param PushContext $context
     * @param Image $image
     *
     * @throws DockerException
     */
    public function push(PushContext $context, Image $image)
    {
        return;
    }
}
