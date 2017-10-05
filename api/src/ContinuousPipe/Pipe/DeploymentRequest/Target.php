<?php

namespace ContinuousPipe\Pipe\DeploymentRequest;

class Target
{
    /**
     * Environment name.
     *
     * @var string
     */
    private $environmentName;

    /**
     * Environment labels.
     *
     * @var array
     */
    private $environmentLabels;

    /**
     * Identifier of the cluster.
     *
     * @var string
     */
    private $clusterIdentifier;

    /**
     * @return string
     */
    public function getEnvironmentName()
    {
        return $this->environmentName;
    }

    /**
     * @return string
     */
    public function getClusterIdentifier()
    {
        return $this->clusterIdentifier;
    }

    /**
     * @return array
     */
    public function getEnvironmentLabels()
    {
        return $this->environmentLabels ?: [];
    }
}
