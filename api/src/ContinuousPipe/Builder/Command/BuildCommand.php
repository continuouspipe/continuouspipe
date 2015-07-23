<?php

namespace ContinuousPipe\Builder\Command;

use ContinuousPipe\Builder\Build;
use JMS\Serializer\Annotation as JMS;

class BuildCommand
{
    /**
     * @JMS\Type("ContinuousPipe\Builder\Build")
     *
     * @var Build
     */
    private $build;

    /**
     * Create a command from a build object.
     *
     * @param Build $build
     *
     * @return BuildCommand
     */
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
