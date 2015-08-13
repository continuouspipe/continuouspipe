<?php

namespace ContinuousPipe\Pipe;

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
     * @param Deployment $deployment
     * @param Logger     $logger
     */
    public function __construct(Deployment $deployment, Logger $logger)
    {
        $this->deployment = $deployment;
        $this->logger = $logger;
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
}
