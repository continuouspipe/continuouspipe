<?php

namespace ContinuousPipe\Builder;

use Ramsey\Uuid\Uuid;

interface BuildRepository
{
    /**
     * Save this build.
     *
     * @param Build $build
     *
     * @return Build
     */
    public function save(Build $build);

    /**
     * @param Uuid $uuid
     *
     * @throws BuildNotFound
     *
     * @return Build
     */
    public function find(Uuid $uuid);
}
