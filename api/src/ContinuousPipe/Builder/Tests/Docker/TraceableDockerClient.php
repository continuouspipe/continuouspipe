<?php

namespace ContinuousPipe\Builder\Tests\Docker;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Docker\BuildContext;
use ContinuousPipe\Builder\Docker\DockerFacade;
use ContinuousPipe\Builder\Docker\PushContext;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Logger;

class TraceableDockerClient implements DockerFacade
{
    /**
     * @var BuildContext[]
     */
    private $builds = [];

    /**
     * @var Image[]
     */
    private $pushes = [];

    /**
     * @var array
     */
    private $commits = [];

    /**
     * @var DockerFacade
     */
    private $client;

    /**
     * @param DockerFacade $client
     */
    public function __construct(DockerFacade $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function build(BuildContext $context, Archive $archive) : Image
    {
        $image = $this->client->build($context, $archive);

        $this->builds[] = $context;

        return $image;
    }

    /**
     * {@inheritdoc}
     */
    public function push(PushContext $context, Image $image)
    {
        $this->client->push($context, $image);

        $this->pushes[] = $image;
    }

    /**
     * @return BuildContext[]
     */
    public function getBuilds()
    {
        return $this->builds;
    }

    /**
     * @return \ContinuousPipe\Builder\Image[]
     */
    public function getPushes()
    {
        return $this->pushes;
    }

    /**
     * @return array
     */
    public function getCommits()
    {
        return $this->commits;
    }
}
