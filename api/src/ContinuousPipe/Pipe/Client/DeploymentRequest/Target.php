<?php

namespace ContinuousPipe\Pipe\Client\DeploymentRequest;

use JMS\Serializer\Annotation as JMS;

class Target
{
    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("environmentName")
     *
     * @var string
     */
    private $environmentName;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("clusterIdentifier")
     *
     * @var string
     */
    private $clusterIdentifier;

    /**
     * Environment labels.
     *
     * @JMS\Type("array<string,string>")
     * @JMS\SerializedName("environmentLabels")
     *
     * @var string
     */
    private $environmentLabels;

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
     * @return string
     */
    public function getEnvironmentLabels()
    {
        return $this->environmentLabels;
    }

    public function withClusterIdentifier(string $clusterIdentifier) : self
    {
        $target = clone $this;
        $target->clusterIdentifier = $clusterIdentifier;

        return $target;
    }
}
