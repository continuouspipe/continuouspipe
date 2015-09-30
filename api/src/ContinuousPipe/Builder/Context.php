<?php

namespace ContinuousPipe\Builder;

class Context
{
    /**
     * @var string
     */
    private $dockerFilePath;

    /**
     * @var string
     */
    private $repositorySubDirectory;

    /**
     * @param string $dockerFilePath
     * @param string $repositorySubDirectory
     */
    public function __construct($dockerFilePath, $repositorySubDirectory)
    {
        $this->dockerFilePath = $dockerFilePath;
        $this->repositorySubDirectory = $repositorySubDirectory;
    }

    /**
     * @return string
     */
    public function getDockerFilePath()
    {
        return $this->dockerFilePath;
    }

    /**
     * @return string
     */
    public function getRepositorySubDirectory()
    {
        return $this->repositorySubDirectory;
    }
}
