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
     * Name of the provider.
     *
     * @var string
     */
    private $providerName;

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
