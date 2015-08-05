<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\RegistryCredentials;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Raw;
use LogStream\Node\Text;

class DockerBuilder implements Builder
{
    /**
     * @var ArchiveBuilder
     */
    private $archiveBuilder;

    /**
     * @var Client
     */
    private $dockerClient;

    /**
     * @var null|string
     */
    private $defaultCredentials;

    /**
     * @param ArchiveBuilder $archiveBuilder
     * @param Client $dockerClient
     * @param string $defaultCredentials
     */
    public function __construct(ArchiveBuilder $archiveBuilder, Client $dockerClient, $defaultCredentials = null)
    {
        $this->archiveBuilder = $archiveBuilder;
        $this->dockerClient = $dockerClient;
        $this->defaultCredentials = $defaultCredentials;
    }

    /**
     * {@inheritdoc}
     */
    public function build(Build $build, Logger $logger)
    {
        $request = $build->getRequest();
        $repository = $request->getRepository();
        $targetImage = $request->getImage();

        $archive = $this->archiveBuilder->getArchive($repository, $logger);
        $this->dockerClient->build($archive, $targetImage, $logger);

        $credentials = $this->getCredentials();
        $this->dockerClient->push($targetImage, $credentials, $logger);
    }

    /**
     * @return RegistryCredentials
     */
    private function getCredentials()
    {
        return RegistryCredentials::fromAuthenticationString($this->defaultCredentials);
    }
}
