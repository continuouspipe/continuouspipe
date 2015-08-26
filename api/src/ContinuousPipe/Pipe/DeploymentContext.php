<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Adapter\Provider;
use ContinuousPipe\Model\Environment;
use LogStream\Logger;

class DeploymentContext
{
    /**
     * @var View\Deployment
     */
    private $deployment;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var array
     */
    private $bag = [];

    /**
     * @param View\Deployment $deployment
     * @param Provider        $provider
     * @param Logger          $logger
     * @param Environment     $environment
     */
    public function __construct(View\Deployment $deployment, Provider $provider, Logger $logger, Environment $environment)
    {
        $this->deployment = $deployment;
        $this->logger = $logger;
        $this->provider = $provider;
        $this->environment = $environment;
    }

    /**
     * @return View\Deployment
     */
    public function getDeployment()
    {
        return $this->deployment;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    public function add($key, $value)
    {
        $this->bag[$key] = $value;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->bag);
    }

    public function get($key)
    {
        return $this->bag[$key];
    }
}
