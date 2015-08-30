<?php

namespace ContinuousPipe\Pipe\DeploymentRequest;

class Specification
{
    /**
     * Contents of the Docker-Compose file.
     *
     * @var string
     */
    private $dockerComposeContents;

    /**
     * @return string
     */
    public function getDockerComposeContents()
    {
        return $this->dockerComposeContents;
    }
}
