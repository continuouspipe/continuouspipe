<?php

namespace ContinuousPipe\Security\Credentials;

use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Bucket
{
    /**
     * @var UuidInterface
     */
    private $uuid;

    /**
     * @var DockerRegistry[]|ArrayCollection
     */
    private $dockerRegistries;

    /**
     * @var GitHubToken[]|ArrayCollection
     */
    private $gitHubTokens;

    /**
     * @var Cluster[]|ArrayCollection
     */
    private $clusters;

    /**
     * @param UuidInterface $uuid
     */
    public function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return DockerRegistry[]|ArrayCollection
     */
    public function getDockerRegistries()
    {
        if (null === $this->dockerRegistries) {
            $this->dockerRegistries = new ArrayCollection();
        }

        return $this->dockerRegistries;
    }

    /**
     * @return GitHubToken[]|ArrayCollection
     */
    public function getGitHubTokens()
    {
        if (null === $this->gitHubTokens) {
            $this->gitHubTokens = new ArrayCollection();
        }

        return $this->gitHubTokens;
    }

    /**
     * @return Cluster[]|ArrayCollection
     */
    public function getClusters()
    {
        if (null === $this->clusters) {
            $this->clusters = new ArrayCollection();
        }

        return $this->clusters;
    }

    /**
     * @return UuidInterface
     */
    public function getUuid()
    {
        if (is_string($this->uuid)) {
            $this->uuid = Uuid::fromString($this->uuid);
        }

        return $this->uuid;
    }
}
