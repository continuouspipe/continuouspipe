<?php

namespace ContinuousPipe\River\Task\Run;

use ContinuousPipe\River\Pipe\EnvironmentAwareConfiguration;
use JMS\Serializer\Annotation as JMS;

class RunTaskConfiguration implements EnvironmentAwareConfiguration
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $clusterIdentifier;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $image;

    /**
     * @JMS\Type("array<string>")
     *
     * @var array
     */
    private $commands;

    /**
     * @JMS\Type("array<string,string>")
     *
     * @var array
     */
    private $environmentVariables;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $environmentName;

    /**
     * @param string $clusterIdentifier
     * @param string $image
     * @param array  $commands
     * @param array  $environmentVariables
     * @param string $environmentName
     */
    public function __construct($clusterIdentifier, $image, array $commands, array $environmentVariables, $environmentName)
    {
        $this->clusterIdentifier = $clusterIdentifier;
        $this->image = $image;
        $this->commands = $commands;
        $this->environmentVariables = $environmentVariables;
        $this->environmentName = $environmentName;
    }

    /**
     * @return string
     */
    public function getClusterIdentifier()
    {
        return $this->clusterIdentifier;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * @return array
     */
    public function getEnvironmentVariables()
    {
        return $this->environmentVariables;
    }

    /**
     * @param string $name
     * @param string $address
     */
    public function addEnvironmentVariable($name, $address)
    {
        $this->environmentVariables[$name] = $address;
    }

    /**
     * @param array $environmentVariables
     */
    public function setEnvironmentVariables(array $environmentVariables)
    {
        $this->environmentVariables = $environmentVariables;
    }

    /**
     * @return string
     */
    public function getEnvironmentName()
    {
        return $this->environmentName;
    }
}
