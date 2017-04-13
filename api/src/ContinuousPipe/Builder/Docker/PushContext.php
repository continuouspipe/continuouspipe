<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Engine;
use ContinuousPipe\Builder\RegistryCredentials;

class PushContext extends DockerContext
{
    /**
     * @var RegistryCredentials
     */
    private $registryCredentials;
    /**
     * @var Engine
     */
    private $engine;

    public function __construct(string $logStreamIdentifier, RegistryCredentials $registryCredentials, Engine $engine)
    {
        parent::__construct($logStreamIdentifier);

        $this->registryCredentials = $registryCredentials;
        $this->engine = $engine;
    }


    public function getCredentials() : RegistryCredentials
    {
        return $this->registryCredentials;
    }

    public function getEngine()
    {
        return $this->engine;
    }
}
