<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Builder;
use LogStream\Logger;

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
     * @var CredentialsRepository
     */
    private $credentialsRepository;

    /**
     * @param ArchiveBuilder        $archiveBuilder
     * @param Client                $dockerClient
     * @param CredentialsRepository $credentialsRepository
     */
    public function __construct(ArchiveBuilder $archiveBuilder, Client $dockerClient, CredentialsRepository $credentialsRepository)
    {
        $this->archiveBuilder = $archiveBuilder;
        $this->dockerClient = $dockerClient;
        $this->credentialsRepository = $credentialsRepository;
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

        $credentials = $this->credentialsRepository->findByImage($targetImage, $build->getUser());
        $this->dockerClient->push($targetImage, $credentials, $logger);
    }
}
