<?php

namespace ContinuousPipe\Pipe\Client;

use JMS\Serializer\Annotation as JMS;
use LogStream\Log;

class DeploymentRequest
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
     * @JMS\SerializedName("providerName")
     *
     * @var string
     */
    private $providerName;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("dockerComposeContents")
     *
     * @var string
     */
    private $dockerComposeContents;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("notificationCallbackUrl")
     *
     * @var string
     */
    private $notificationCallbackUrl;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("logId")
     *
     * @var string
     */
    private $logId;

    /**
     * @param string $environmentName
     * @param string $providerName
     * @param string $dockerComposeContents
     * @param string $notificationCallbackUrl
     * @param string $logId
     */
    public function __construct($environmentName, $providerName, $dockerComposeContents, $notificationCallbackUrl, $logId)
    {
        $this->environmentName = $environmentName;
        $this->providerName = $providerName;
        $this->dockerComposeContents = $dockerComposeContents;
        $this->notificationCallbackUrl = $notificationCallbackUrl;
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

    /**
     * @return string
     */
    public function getNotificationCallbackUrl()
    {
        return $this->notificationCallbackUrl;
    }
}
