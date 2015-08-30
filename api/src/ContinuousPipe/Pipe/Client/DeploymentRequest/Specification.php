<?php

namespace ContinuousPipe\Pipe\Client\DeploymentRequest;

class Specification
{
    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("dockerComposeContents")
     *
     * @var string
     */
    private $dockerComposeContents;

    /**
     * @param string $dockerComposeContents
     */
    public function __construct($dockerComposeContents)
    {
        $this->dockerComposeContents = $dockerComposeContents;
    }

    /**
     * @return string
     */
    public function getDockerComposeContents()
    {
        return $this->dockerComposeContents;
    }
}
