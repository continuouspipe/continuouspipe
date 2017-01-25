<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Context;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use Ramsey\Uuid\UuidInterface;

class BuildContext extends DockerContext
{
    private $context;
    private $environment;
    private $image;

    /**
     * @var DockerRegistry[]
     */
    private $dockerRegistries;

    public function __construct(
        string $logStreamIdentifier,
        Context $context,
        array $environment,
        array $dockerRegistries,
        Image $image
    ) {
        parent::__construct($logStreamIdentifier);

        $this->context = $context;
        $this->environment = $environment;
        $this->dockerRegistries = $dockerRegistries;
        $this->image = $image;
    }

    /**
     * @return Image
     */
    public function getImage() : Image
    {
        return $this->image;
    }

    public function getContext() : Context
    {
        return $this->context;
    }

    public function getEnvironment() : array
    {
        return $this->environment;
    }

    /**
     * @return DockerRegistry[]
     */
    public function getDockerRegistries() : array
    {
        return $this->dockerRegistries;
    }
}
