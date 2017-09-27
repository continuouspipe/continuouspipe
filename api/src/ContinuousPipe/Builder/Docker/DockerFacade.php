<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Image;

interface DockerFacade
{
    /**
     * @param BuildContext $context
     * @param Archive      $archive
     *
     * @throws DockerException
     *
     * @return Image
     */
    public function build(BuildContext $context, Archive $archive) : Image;

    /**
     * @param PushContext $context
     * @param Image       $image
     *
     * @throws DockerException
     */
    public function push(PushContext $context, Image $image);
}
