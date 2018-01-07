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
     * @param string $environmentName
     * @param string $clusterIdentifier
     * @param array  $environmentLabels
     */
    public function __construct($environmentName, $clusterIdentifier, array $environmentLabels = [])
    {
        $this->environmentName = $environmentName;
        $this->clusterIdentifier = $clusterIdentifier;
        $this->environmentLabels = $environmentLabels;
    }

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

    public function withClusterIdentifier(string $clusterIdentifier) : self
    {
        $target = clone $this;
        $target->clusterIdentifier = $clusterIdentifier;

        return $target;
    }
}
