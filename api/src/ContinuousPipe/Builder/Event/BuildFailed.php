<?php

namespace ContinuousPipe\Builder\Event;

use ContinuousPipe\Builder\Build;

class BuildFailed implements BuildEvent
{
    /**
     * @var Build
     */
    private $build;

    /**
     * @param Build $build
     */
    public function __construct(Build $build)
    {
        $this->build = $build;
    }

    /**
     * @return Build
     */
    public function getBuild()
    {
        return $this->build;
    }
}
