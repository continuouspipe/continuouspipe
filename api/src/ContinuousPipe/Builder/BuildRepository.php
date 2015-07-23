<?php

namespace ContinuousPipe\Builder;

interface BuildRepository
{
    /**
     * Save this build.
     *
     * @param Build $build
     * @return Build
     */
    public function save(Build $build);
}
