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
     * Find environments by labels.
     *
     * @param array $labels
     *
     * @return Environment[]
     */
    public function findByLabels(array $labels);

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
