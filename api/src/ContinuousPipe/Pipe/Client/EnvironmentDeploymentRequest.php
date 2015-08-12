<?php

namespace ContinuousPipe\Pipe\Client;

use JMS\Serializer\Annotation as JMS;
use LogStream\Log;

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
     * @JMS\SerializedName("logId")
     *
     * @var string
     */
    private $logId;

    /**
     * @param string $environmentName
     * @param string $providerName
     * @param string $dockerComposeContents
     * @param string $logId
     */
    public function __construct($environmentName, $providerName, $dockerComposeContents, $logId)
    {
        $this->environmentName = $environmentName;
        $this->providerName = $providerName;
        $this->dockerComposeContents = $dockerComposeContents;
        $this->logId = $logId;
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

    /**
     * @return string
     */
    public function getLogId()
    {
        return $this->logId;
    }
}
