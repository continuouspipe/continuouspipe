<?php

namespace Builder;

use Builder\Docker\Client;

class DockerBuilder
{
    /**
     * @var ArchiveBuilder
     */
    private $archiveBuilder;
    /**
     * @var Client
     */
    private $dockerClient;

    public function __construct(ArchiveBuilder $archiveBuilder, Client $dockerClient)
    {
        $this->archiveBuilder = $archiveBuilder;
        $this->dockerClient = $dockerClient;
    }

    public function build(Repository $repository, Image $targetImage)
    {
        $archive = $this->archiveBuilder->getArchive($repository);
        $this->dockerClient->build($archive, $targetImage);
        $this->dockerClient->push($targetImage);
    }
}
