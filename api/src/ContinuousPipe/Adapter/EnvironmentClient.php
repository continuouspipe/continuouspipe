<?php

namespace ContinuousPipe\Adapter;

use ContinuousPipe\Model\Environment;
use LogStream\Logger;

interface EnvironmentClient
{
    /**
     * @param Environment $environment
     * @param Logger      $logger
     *
     * @return Environment
     */
    public function createOrUpdate(Environment $environment, Logger $logger);

    /**
     * List environments.
     *
     * @return Environment
     */
    public function findAll();
}
