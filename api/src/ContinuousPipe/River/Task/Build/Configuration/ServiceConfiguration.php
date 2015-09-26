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
     * @JMS\Type("string")
     *
     * @var string
     */
    private $tag;

    /**
     * @param string $image
     * @param string $tag
     * @param string $buildDirectory
     * @param string $dockerFilePath
     */
    public function __construct($image, $tag, $buildDirectory, $dockerFilePath)
    {
        $this->image = $image;
        $this->buildDirectory = $buildDirectory;
        $this->dockerFilePath = $dockerFilePath;
        $this->tag = $tag;
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
    public function getTag()
    {
        return $this->tag;
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
