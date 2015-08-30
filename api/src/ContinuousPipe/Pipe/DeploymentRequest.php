<?php

namespace ContinuousPipe\Pipe;

class DeploymentRequest
{
    /**
     * Environment name.
     *
     * @var string
     */
    private $environmentName;

    /**
     * Name of the provider.
     *
     * @var string
     */
    private $providerName;

    /**
     * Contents of the Docker-Compose file.
     *
     * @var string
     */
    private $dockerComposeContents;

    /**
     * URL of a callback to have notification about the status of the deployment.
     *
     * @var string
     */
    private $notificationCallbackUrl;

    /**
     * @var string
     */
    private $logId;

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
    public function getNotificationCallbackUrl()
    {
        return $this->notificationCallbackUrl;
    }

    /**
     * @return string
     */
    public function getLogId()
    {
        return $this->logId;
    }
}
