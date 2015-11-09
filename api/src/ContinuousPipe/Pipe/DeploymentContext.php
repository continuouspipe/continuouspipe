<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Adapter\Provider;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\Security\Credentials\Cluster;
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
     * @var Environment
     */
    private $environment;

    /**
     * @var Cluster
     */
    private $cluster;

    /**
     * @var array
     */
    private $bag = [];

    /**
     * @param View\Deployment $deployment
     * @param Cluster $cluster
     * @param Log             $log
     * @param Environment     $environment
     */
    public function __construct(View\Deployment $deployment, Cluster $cluster = null, Log $log = null, Environment $environment = null)
    {
        $this->deployment = $deployment;
        $this->log = $log;
        $this->environment = $environment;
        $this->cluster = $cluster;
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
     *
     * @deprecated
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @return Cluster
     */
    public function getCluster()
    {
        return $this->cluster;
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
