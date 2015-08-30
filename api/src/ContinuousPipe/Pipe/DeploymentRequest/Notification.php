<?php

namespace ContinuousPipe\Pipe\DeploymentRequest;

class Notification
{
    /**
     * URL of a callback to have notification about the status of the deployment.
     *
     * @var string
     */
    private $httpCallbackUrl;

    /**
     * @var string
     */
    private $logStreamParentId;

    /**
     * @param string $httpCallbackUrl
     * @param string $logStreamParentId
     */
    public function __construct($httpCallbackUrl = null, $logStreamParentId = null)
    {
        $this->httpCallbackUrl = $httpCallbackUrl;
        $this->logStreamParentId = $logStreamParentId;
    }

    /**
     * @return string
     */
    public function getHttpCallbackUrl()
    {
        return $this->httpCallbackUrl;
    }

    /**
     * @return string
     */
    public function getLogStreamParentId()
    {
        return $this->logStreamParentId;
    }
}
