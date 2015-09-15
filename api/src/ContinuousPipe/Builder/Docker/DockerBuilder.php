<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Archive\ArchiveCreationException;
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

        try {
            $archive = $this->archiveBuilder->getArchive($request, $build->getUser(), $logger);
        } catch (ArchiveCreationException $e) {
            throw new BuildException(sprintf('Unable to create archive: %s', $e->getMessage()), $e->getCode(), $e);
        }

        $this->dockerClient->build($archive, $request, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function push(Build $build, Logger $logger)
    {
        $request = $build->getRequest();
        $targetImage = $request->getImage();

        try {
            $credentials = $this->credentialsRepository->findByImage($targetImage, $build->getUser());
        } catch (CredentialsNotFound $e) {
            throw new BuildException('Credentials not found.', $e->getCode(), $e);
        }

        $this->dockerClient->push($targetImage, $credentials, $logger);
    }
}
