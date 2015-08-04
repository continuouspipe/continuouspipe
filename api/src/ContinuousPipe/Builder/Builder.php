<?php

namespace ContinuousPipe\Builder;

use LogStream\Logger;

interface Builder
{
    /**
     * Run the given build.
     *
     * @param Build  $build
     * @param Logger $logger
     */
    public function build(Build $build, Logger $logger);
}
