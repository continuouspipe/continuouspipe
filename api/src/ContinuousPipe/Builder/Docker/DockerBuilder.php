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
use ContinuousPipe\Security\Authenticator\CredentialsNotFound;
use LogStream\Log;
use LogStream\Logger;
use LogStream\LoggerFactory;
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
     * @var CredentialsRepository
     */
    private $credentialsRepository;

    /**
     * @var CommandExtractor
     */
    private $commandExtractor;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param ArchiveBuilder        $archiveBuilder
     * @param Client                $dockerClient
     * @param CredentialsRepository $credentialsRepository
     * @param CommandExtractor      $commandExtractor
     * @param LoggerFactory         $loggerFactory
     */
    public function __construct(ArchiveBuilder $archiveBuilder, Client $dockerClient, CredentialsRepository $credentialsRepository, CommandExtractor $commandExtractor, LoggerFactory $loggerFactory)
    {
        $this->archiveBuilder = $archiveBuilder;
        $this->dockerClient = $dockerClient;
        $this->credentialsRepository = $credentialsRepository;
        $this->commandExtractor = $commandExtractor;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function build(Build $build, Logger $logger)
    {
        $request = $build->getRequest();

        try {
            $archive = $this->archiveBuilder->getArchive($request, $logger);
        } catch (ArchiveCreationException $e) {
            throw new BuildException(sprintf('Unable to create archive: %s', $e->getMessage()), $e->getCode(), $e);
        }

        $commands = $this->commandExtractor->getCommands($build, $archive);
        if (count($commands) > 0) {
            $archive = $this->commandExtractor->getArchiveWithStrippedDockerfile($build, $archive);
        }

        try {
            $image = $this->dockerClient->build($archive, $request, $logger);
        } catch (DockerException $e) {
            throw new BuildException(
                sprintf('Unable to build image: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        } finally {
            $archive->delete();
        }

        try {
            $this->runCommandsAndCommitImage($logger, $image, $commands);
        } catch (DockerException $e) {
            throw new BuildException(
                sprintf('Unable to run isolated command: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

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
            $credentials = $this->credentialsRepository->findByImage($targetImage, $request->getCredentialsBucket());
        } catch (CredentialsNotFound $e) {
            throw new BuildException(sprintf('Credentials not found: %s', $e->getMessage()), $e->getCode(), $e);
        }

        try {
            $this->dockerClient->push($targetImage, $credentials, $logger);
        } catch (DockerException $e) {
            throw new BuildException(
                sprintf('Unable to push image: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param Logger $logger
     * @param Image  $image
     * @param array  $commands
     *
     * @throws DockerException
     *
     * @return Image
     */
    private function runCommandsAndCommitImage(Logger $logger, Image $image, array $commands)
    {
        if (count($commands) == 0) {
            return $image;
        }

        $logger = $logger->child(new Text('Running extra build commands'))->updateStatus(Log::RUNNING);

        try {
            foreach ($commands as $command) {
                $image = $this->dockerClient->runAndCommit($image, $logger, $command);
            }

            $logger->updateStatus(Log::SUCCESS);
        } catch (DockerException $e) {
            $logger->updateStatus(Log::FAILURE);

            throw $e;
        }

        return $image;
    }
}
