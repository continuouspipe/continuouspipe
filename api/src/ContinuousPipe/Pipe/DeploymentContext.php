<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Adapter\Provider;
use ContinuousPipe\Model\Environment;
use LogStream\Log;

class DeploymentContext
{
    /**
     * @var View\Deployment
     */
    private $deployment;

    /**
     * @var Log
     */
    private $log;

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
     * @param Log             $log
     * @param Environment     $environment
     */
    public function __construct(View\Deployment $deployment, Provider $provider = null, Log $log = null, Environment $environment = null)
    {
        $this->deployment = $deployment;
        $this->log = $log;
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
     * @return Log
     */
    public function getLog()
    {
        return $this->log;
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

    /**
     * Add a new value in the context.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function add($key, $value)
    {
        $this->bag[$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->bag);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->bag[$key];
    }
}
