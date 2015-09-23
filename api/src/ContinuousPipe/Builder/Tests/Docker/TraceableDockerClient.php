<?php

namespace ContinuousPipe\Builder\Tests\Docker;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Docker\Client;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Logger;

class TraceableDockerClient implements Client
{
    /**
     * @var BuildRequest[]
     */
    private $builds = [];

    /**
     * @var Image[]
     */
    private $pushes = [];

    /**
     * @var array
     */
    private $runs = [];

    /**
     * @var array
     */
    private $commits = [];

    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function build(Archive $archive, BuildRequest $request, Logger $logger)
    {
        $image = $this->client->build($archive, $request, $logger);

        $this->builds[] = $request;

        return $image;
    }

    /**
     * {@inheritdoc}
     */
    public function push(Image $image, RegistryCredentials $credentials, Logger $logger)
    {
        $this->client->push($image, $credentials, $logger);

        $this->pushes[] = $image;
    }

    /**
     * {@inheritdoc}
     */
    public function runAndCommit(Image $image, Logger $logger, $command)
    {
        $image = $this->client->runAndCommit($image, $logger, $command);

        $this->runs[] = ['image' => $image, 'command' => $command];
        $this->commits[] = ['image' => $image];

        return $image;
    }

    /**
     * @return \ContinuousPipe\Builder\Request\BuildRequest[]
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
    public function getRuns()
    {
        return $this->runs;
    }

    /**
     * @return array
     */
    public function getCommits()
    {
        return $this->commits;
    }
}
