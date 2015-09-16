<?php

namespace ContinuousPipe\Builder\IsolatedCommands;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Build;

interface CommandExtractor
{
    /**
     * @param Build   $build
     * @param Archive $archive
     *
     * @return array
     */
    public function getCommands(Build $build, Archive $archive);

    /**
     * @param Build   $build
     * @param Archive $archive
     *
     * @return Archive
     */
    public function getArchiveWithStrippedDockerfile(Build $build, Archive $archive);
}
