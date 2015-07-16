<?php

namespace ContinuousPipe\Builder\Command;

use ContinuousPipe\Builder\Build;

class BuildCommand
{
    /**
     * @var Build
     */
    private $build;

    public static function forBuild(Build $build)
    {
        $command = new self();
        $command->build = $build;

        return $command;
    }

    /**
     * @return Build
     */
    public function getBuild()
    {
        return $this->build;
    }
}
