<?php

namespace ContinuousPipe\Builder\Logging\Docker;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Docker\Client;
use ContinuousPipe\Builder\Docker\DockerException;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Raw;
use LogStream\Node\Text;

class LoggedDockerClient implements Client
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param Client        $client
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(Client $client, LoggerFactory $loggerFactory)
    {
        $this->client = $client;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function build(Archive $archive, BuildRequest $request, Logger $logger)
    {
        $log = $logger->append(new Text('Start Docker build'));
        $buildLogger = $this->loggerFactory->from($log);
        $buildLogger->start();

        $raw = $buildLogger->append(new Raw());
        $rawBuildLogger = $this->loggerFactory->from($raw);

        try {
            $image = $this->client->build($archive, $request, $rawBuildLogger);
            $buildLogger->success();
        } catch (DockerException $e) {
            $buildLogger->failure();

            throw $e;
        }

        return $image;
    }

    /**
     * {@inheritdoc}
     */
    public function push(Image $image, RegistryCredentials $credentials, Logger $logger)
    {
        $log = $logger->append(new Text('Pushing Docker image'));
        $pushLogger = $this->loggerFactory->from($log);
        $pushLogger->start();

        $raw = $pushLogger->append(new Raw());
        $rawPushLogger = $this->loggerFactory->from($raw);

        try {
            $this->client->push($image, $credentials, $rawPushLogger);

            $pushLogger->success();
        } catch (DockerException $e) {
            $pushLogger->append(new Text($e->getMessage()));
            $pushLogger->failure();

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function runAndCommit(Image $image, Logger $logger, $command)
    {
        return $this->client->runAndCommit($image, $logger, $command);
    }
}
