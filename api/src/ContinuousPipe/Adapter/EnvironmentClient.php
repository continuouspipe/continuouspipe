<?php

namespace ContinuousPipe\Adapter;

use ContinuousPipe\Model\Environment;

interface EnvironmentClient
{
    /**
     * List environments.
     *
     * @return Environment
     */
    public function findAll();
}
