<?php

namespace ContinuousPipe\River\Task\Run;

use ContinuousPipe\Model\Component\Volume;
use ContinuousPipe\Model\Component\VolumeMount;
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
     * @var Volume[]
     */
    private $volumes;
    /**
     * @var VolumeMount[]
     */
    private $volumeMounts;

    /**
     * @param string $clusterIdentifier
     * @param string $image
     * @param array $commands
     * @param array $environmentVariables
     * @param string $environmentName
     * @param Volume[] $volumes
     * @param VolumeMount[] $volumeMounts
     */
    public function __construct($clusterIdentifier, $image, array $commands, array $environmentVariables, $environmentName, array $volumes = [], array $volumeMounts = [])
    {
        $this->clusterIdentifier = $clusterIdentifier;
        $this->image = $image;
        $this->commands = $commands;
        $this->environmentVariables = $environmentVariables;
        $this->environmentName = $environmentName;
        $this->volumes = $volumes;
        $this->volumeMounts = $volumeMounts;
    }

    /**
     * {@inheritdoc}
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

    /**
     * @return Volume[]
     */
    public function getVolumes(): array
    {
        return $this->volumes ?: [];
    }

    /**
     * @return VolumeMount[]
     */
    public function getVolumeMounts(): array
    {
        return $this->volumeMounts ?: [];
    }
}
