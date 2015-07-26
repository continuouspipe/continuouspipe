<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Docker\Client;
use ContinuousPipe\LogStream\Log;
use ContinuousPipe\LogStream\LoggerFactory;

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
        $build->updateStatus(Build::STATUS_RUNNING);
        $build = $this->buildRepository->save($build);

        try {
            $this->runBuild($build);

            $build->updateStatus(Build::STATUS_SUCCESS);
        } catch (\Exception $e) {
            $logger = $this->loggerFactory->createLogger($build);
            $logger->log(Log::exception($e));

            $build->updateStatus(Build::STATUS_ERROR);
        } finally {
            $build = $this->buildRepository->save($build);
        }

        return $build;
    }

    /**
     * @param Build $build
     */
    private function runBuild(Build $build)
    {
        $request = $build->getRequest();
        $repository = $request->getRepository();
        $targetImage = $request->getImage();

        $logger = $this->loggerFactory->createLogger($build);

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
