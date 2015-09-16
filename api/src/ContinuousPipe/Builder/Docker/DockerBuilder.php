<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Archive\ArchiveCreationException;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\BuildException;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\IsolatedCommands\CommandExtractor;
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
     * @var CommandExtractor
     */
    private $commandExtractor;

    /**
     * @param ArchiveBuilder        $archiveBuilder
     * @param Client                $dockerClient
     * @param CredentialsRepository $credentialsRepository
     * @param CommandExtractor      $commandExtractor
     */
    public function __construct(ArchiveBuilder $archiveBuilder, Client $dockerClient, CredentialsRepository $credentialsRepository, CommandExtractor $commandExtractor)
    {
        $this->archiveBuilder = $archiveBuilder;
        $this->dockerClient = $dockerClient;
        $this->credentialsRepository = $credentialsRepository;
        $this->commandExtractor = $commandExtractor;
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

        $commands = $this->commandExtractor->getCommands($build, $archive);
        if (count($commands) > 0) {
            $archive = $this->commandExtractor->getArchiveWithStrippedDockerfile($build, $archive);
        }

        // Build the image
        $image = $this->dockerClient->build($archive, $request, $logger);

        $this->runCommandsAndCommitImage($image, $commands);

        return $image;
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

    /**
     * @param Image $image
     * @param array $commands
     *
     * @return Image
     */
    private function runCommandsAndCommitImage(Image $image, array $commands)
    {
        if (count($commands) == 0) {
            return $image;
        }

        $container = $this->dockerClient->createContainer($image);
        foreach ($commands as $command) {
            $container = $this->dockerClient->run($container, $command);
        }

        $this->dockerClient->commit($container, $image);

        return $image;
    }
}
