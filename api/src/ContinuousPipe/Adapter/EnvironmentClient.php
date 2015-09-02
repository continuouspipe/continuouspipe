<?php

namespace ContinuousPipe\Adapter;

use ContinuousPipe\Model\Environment;

interface EnvironmentClient
{
    /**
     * List environments.
     *
     * @return Environment[]
     */
    public function findAll();

    /**
     * @param string $identifier
     *
     * @throws EnvironmentNotFound
     *
     * @return Environment
     */
    public function find($identifier);

    /**
     * Delete the given environment.
     *
     * @param Environment $environment
     */
    public function delete(Environment $environment);
}
