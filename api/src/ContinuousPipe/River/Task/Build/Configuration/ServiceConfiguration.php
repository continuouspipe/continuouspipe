<?php

namespace ContinuousPipe\River\Task\Build\Configuration;

use JMS\Serializer\Annotation as JMS;

class ServiceConfiguration
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $image;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $buildDirectory;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $dockerFilePath;

    /**
     * @param string $image
     * @param string $buildDirectory
     * @param string $dockerFilePath
     */
    public function __construct($image, $buildDirectory, $dockerFilePath)
    {
        $this->image = $image;
        $this->buildDirectory = $buildDirectory;
        $this->dockerFilePath = $dockerFilePath;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getBuildDirectory()
    {
        return $this->buildDirectory;
    }

    /**
     * @return string
     */
    public function getDockerFilePath()
    {
        return $this->dockerFilePath;
    }
}
