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
     * @param string $environmentName
     * @param string $clusterIdentifier
     */
    public function __construct($environmentName, $clusterIdentifier)
    {
        $this->environmentName = $environmentName;
        $this->clusterIdentifier = $clusterIdentifier;
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
}
