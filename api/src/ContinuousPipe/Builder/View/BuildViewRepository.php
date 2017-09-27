<?php

namespace ContinuousPipe\Builder\View;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\BuildNotFound;

interface BuildViewRepository
{
    /**
     * @param string $identifier
     *
     * @throws BuildNotFound
     *
     * @return Build
     */
    public function find(string $identifier) : Build;
}
