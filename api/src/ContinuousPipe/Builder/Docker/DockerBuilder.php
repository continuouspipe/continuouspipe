<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\BuildException;
use ContinuousPipe\User\Authenticator\CredentialsNotFound;
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
        $targetImage = $request->getImage();

        $archive = $this->archiveBuilder->getArchive($request, $build->getUser(), $logger);
        $this->dockerClient->build($archive, $request, $logger);

        try {
            $credentials = $this->credentialsRepository->findByImage($targetImage, $build->getUser());
        } catch (CredentialsNotFound $e) {
            throw new BuildException('Credentials not found.', $e->getCode(), $e);
        }

        $this->dockerClient->push($targetImage, $credentials, $logger);
    }
}
