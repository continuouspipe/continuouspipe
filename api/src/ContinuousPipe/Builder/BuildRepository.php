<?php

namespace ContinuousPipe\Builder;

interface BuildRepository
{
    /**
     * Save this build.
     *
     * @param Build $build
     */
    public function save(Build $build);
}
