<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Request\ArchiveSource;
use ContinuousPipe\Security\Credentials\DockerRegistry;

class BuildStepConfiguration
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var ArchiveSource
     */
    private $archive;

    /**
     * @var Image
     */
    private $image;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var array
     */
    private $environment;

    /**
     * @var string
     */
    private $logStreamIdentifier;

    /**
     * @var DockerRegistry[]
     */
    private $dockerRegistries;

    /**
     * @var RegistryCredentials|null
     */
    private $imageRegistryCredentials;

    /**
     * @return Repository|null
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return ArchiveSource|null
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * @return Image|null
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return Context|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return array
     */
    public function getEnvironment()
    {
        return $this->environment ?: [];
    }

    /**
     * @return string|null
     */
    public function getLogStreamIdentifier()
    {
        return $this->logStreamIdentifier;
    }

    /**
     * @return DockerRegistry[]
     */
    public function getDockerRegistries() : array
    {
        return $this->dockerRegistries ?: [];
    }

    /**
     * @return RegistryCredentials|null
     */
    public function getImageRegistryCredentials()
    {
        return $this->imageRegistryCredentials;
    }

    public function withArchiveSource(ArchiveSource $archive) : BuildStepConfiguration
    {
        $step = clone $this;
        $step->archive = $archive;

        return $step;
    }

    public function withLogStreamIdentifier(string $identifier) : BuildStepConfiguration
    {
        $step = clone $this;
        $step->logStreamIdentifier = $identifier;

        return $step;
    }

    public function withDockerRegistries(array $registries) : BuildStepConfiguration
    {
        $step = clone $this;
        $step->dockerRegistries = $registries;

        return $step;
    }

    public function withImageRegistryCredentials(RegistryCredentials $credentials) : BuildStepConfiguration
    {
        $step = clone $this;
        $step->imageRegistryCredentials = $credentials;

        return $step;
    }

    public function withContext(Context $context) : BuildStepConfiguration
    {
        $step = clone $this;
        $step->context = $context;

        return $step;
    }
}
