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
     * @JMS\SerializedName("providerName")
     *
     * @var string
     */
    private $providerName;

    /**
     * @param string $environmentName
     * @param string $providerName
     */
    public function __construct($environmentName, $providerName)
    {
        $this->environmentName = $environmentName;
        $this->providerName = $providerName;
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
}
