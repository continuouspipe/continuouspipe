<?php


namespace ContinuousPipe\Builder\GoogleContainerBuilder;

use ContinuousPipe\Builder\Aggregate\Build;

class SuccessfulInMemoryBuilder implements GoogleContainerBuilderClient
{

    public function createFromRequest(Build $build): GoogleContainerBuild
    {
        return new GoogleContainerBuild(
            $build->getIdentifier()
        );
    }

    public function fetchStatus(GoogleContainerBuild $build): GoogleContainerBuildStatus
    {
        return new GoogleContainerBuildStatus(
            'SUCCESS',
            'Successful imaginary build'
        );
    }
}
