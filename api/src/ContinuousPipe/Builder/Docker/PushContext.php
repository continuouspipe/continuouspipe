<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\RegistryCredentials;

class PushContext extends DockerContext
{
    /**
     * @var RegistryCredentials
     */
    private $registryCredentials;

    public function __construct(string $logStreamIdentifier, RegistryCredentials $registryCredentials)
    {
        parent::__construct($logStreamIdentifier);

        $this->registryCredentials = $registryCredentials;
    }


    public function getCredentials() : RegistryCredentials
    {
        return $this->registryCredentials;
    }
}
