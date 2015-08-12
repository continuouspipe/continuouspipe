<?php

namespace ContinuousPipe\Adapter;

use ContinuousPipe\Model\Environment;

interface EnvironmentClient
{
    /**
     * @param Environment $environment
     *
     * @return Environment
     */
    public function createOrUpdate(Environment $environment);

    /**
     * List environments.
     *
     * @return Environment
     */
    public function findAll();
}
