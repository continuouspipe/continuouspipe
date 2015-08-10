<?php

namespace ContinuousPipe\Pipe\Request;

use JMS\Serializer\Annotation as JMS;

class CreateEnvironmentRequest
{
    /**
     * Environment name.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * Name of the provider.
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("providerName")
     *
     * @var string
     */
    private $providerName;

    /**
     * Contents of the Docker-Compose file.
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("dockerComposeContents")
     *
     * @var string
     */
    private $dockerComposeContents;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
