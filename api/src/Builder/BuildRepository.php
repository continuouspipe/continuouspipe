<?php

namespace Builder;

interface BuildRepository
{
    /**
     * Save this build.
     *
     * @param Build $build
     */
    public function save(Build $build);
}
