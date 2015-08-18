<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Adapter\Provider;
use LogStream\Logger;

class DeploymentContext
{
    /**
     * @var Deployment
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
     * @param Deployment $deployment
     * @param Provider   $provider
     * @param Logger     $logger
     */
    public function __construct(Deployment $deployment, Provider $provider, Logger $logger)
    {
        $this->deployment = $deployment;
        $this->logger = $logger;
        $this->provider = $provider;
    }

    /**
     * @return Deployment
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
}
