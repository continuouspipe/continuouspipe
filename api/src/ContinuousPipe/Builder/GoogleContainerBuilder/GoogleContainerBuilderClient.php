<?php

namespace ContinuousPipe\Builder\GoogleContainerBuilder;

use ContinuousPipe\Builder\Aggregate\Build;

interface GoogleContainerBuilderClient
{
    public function createFromRequest(Build $build) : GoogleContainerBuild;

    public function fetchStatus(GoogleContainerBuild $build) : GoogleContainerBuildStatus;
}
