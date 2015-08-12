<?php

namespace ContinuousPipe\Pipe\Client;

use JMS\Serializer\Annotation as JMS;

class EnvironmentDeploymentRequest
{
    /**
     * @JMS\SerializedName("name")
     *
     * @var string
     */
    private $environmentName;

    /**
     * @JMS\SerializedName("providerName")
     *
     * @var string
     */
    private $providerName;

    /**
     * @JMS\SerializedName("dockerComposeContents")
     *
     * @var string
     */
    private $dockerComposeContents;

    /**
     * @param string $environmentName
     * @param string $providerName
     * @param string $dockerComposeContents
     */
    public function __construct($environmentName, $providerName, $dockerComposeContents)
    {
        $this->environmentName = $environmentName;
        $this->providerName = $providerName;
        $this->dockerComposeContents = $dockerComposeContents;
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
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * @return string
     */
    public function getDockerComposeContents()
    {
        return $this->dockerComposeContents;
    }
}
