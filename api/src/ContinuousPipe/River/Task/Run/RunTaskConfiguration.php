<?php

namespace ContinuousPipe\River\Task\Run;

use JMS\Serializer\Annotation as JMS;

class RunTaskConfiguration
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
    private $environment;

    /**
     * @param string $clusterIdentifier
     * @param string $image
     * @param array  $commands
     * @param array  $environment
     */
    public function __construct($clusterIdentifier, $image, array $commands, array $environment)
    {
        $this->clusterIdentifier = $clusterIdentifier;
        $this->image = $image;
        $this->commands = $commands;
        $this->environment = $environment;
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
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $name
     * @param string $address
     */
    public function addEnvironment($name, $address)
    {
        $this->environment[$name] = $address;
    }

    /**
     * @param array $environment
     */
    public function setEnvironment(array $environment)
    {
        $this->environment = $environment;
    }
}
