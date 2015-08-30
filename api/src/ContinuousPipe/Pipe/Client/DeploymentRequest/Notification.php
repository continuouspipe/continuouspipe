<?php

namespace ContinuousPipe\Pipe\Client\DeploymentRequest;

use JMS\Serializer\Annotation as JMS;

class Notification
{
    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("httpCallbackUrl")
     *
     * @var string
     */
    private $httpCallbackUrl;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("logStreamParentId")
     *
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
