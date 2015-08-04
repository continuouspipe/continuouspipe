<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Docker\Client;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Container;
use LogStream\Node\Text;
use LogStream\WrappedLog;

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
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;
    /**
     * @var BuildRepository
     */
    private $buildRepository;
    /**
     * @var null|string
     */
    private $defaultCredentials;

    /**
     * @param ArchiveBuilder  $archiveBuilder
     * @param Client          $dockerClient
     * @param LoggerFactory   $loggerFactory
     * @param BuildRepository $buildRepository
     * @param string          $defaultCredentials
     */
    public function __construct(ArchiveBuilder $archiveBuilder, Client $dockerClient, LoggerFactory $loggerFactory, BuildRepository $buildRepository, $defaultCredentials = null)
    {
        $this->archiveBuilder = $archiveBuilder;
        $this->dockerClient = $dockerClient;
        $this->loggerFactory = $loggerFactory;
        $this->buildRepository = $buildRepository;
        $this->defaultCredentials = $defaultCredentials;
    }

    /**
     * @param Build $build
     *
     * @return Build
     *
     * @throws \Exception
     */
    public function build(Build $build)
    {
        $logger = $this->getLogger($build);
        $logger->start();

        $build->updateStatus(Build::STATUS_RUNNING);
        $build = $this->buildRepository->save($build);

        try {
            $this->runBuild($build, $logger);

            $build->updateStatus(Build::STATUS_SUCCESS);
        } catch (\Exception $e) {
            $logger->append(new Text($e->getMessage()));

            $build->updateStatus(Build::STATUS_ERROR);
        } finally {
            $build = $this->buildRepository->save($build);
        }

        return $build;
    }

    /**
     * @param Build $build
     * @param Logger $logger
     */
    private function runBuild(Build $build, Logger $logger)
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
     * Get logger for that given build.
     *
     * @param Build $build
     *
     * @return Logger
     */
    private function getLogger(Build $build)
    {
        $logging = $build->getRequest()->getLogging();

        if ($logStream = $logging->getLogstream()) {
            return $this->loggerFactory->from(
                new WrappedLog($logStream->getParentLogIdentifier(), new Container())
            );
        }

        return null;
    }

    /**
     * @return RegistryCredentials
     */
    private function getCredentials()
    {
        return RegistryCredentials::fromAuthenticationString($this->defaultCredentials);
    }
}
