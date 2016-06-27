<?php

namespace ContinuousPipe\Builder\Logging\Docker;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Docker\Client;
use ContinuousPipe\Builder\Docker\DockerException;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Log;
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
        $title = sprintf('Building Docker image <code>%s</code>', $this->getImageName($request->getImage()));
        $logger = $logger->child(new Text($title))->updateStatus(Log::RUNNING);

        try {
            $image = $this->client->build($archive, $request, $logger->child(new Raw()));

            $logger->updateStatus(Log::SUCCESS);
        } catch (DockerException $e) {
            $logger->updateStatus(Log::FAILURE);

            throw $e;
        }

        return $image;
    }

    /**
     * {@inheritdoc}
     */
    public function push(Image $image, RegistryCredentials $credentials, Logger $logger)
    {
        $title = sprintf('Pushing Docker image <code>%s</code>', $this->getImageName($image));
        $logger = $logger->child(new Text($title))->updateStatus(Log::RUNNING);

        try {
            $this->client->push($image, $credentials, $logger->child(new Raw()));

            $logger->updateStatus(Log::SUCCESS);
        } catch (DockerException $e) {
            $logger->child(new Text($e->getMessage()))->updateStatus(Log::FAILURE);
            $logger->updateStatus(Log::FAILURE);

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function runAndCommit(Image $image, Logger $logger, $command)
    {
        $logger = $logger->child(new Text(sprintf('Running "%s"', $command)))->updateStatus(Log::RUNNING);

        try {
            $image = $this->client->runAndCommit($image, $logger->child(new Raw()), $command);

            $logger->updateStatus(Log::SUCCESS);
        } catch (DockerException $e) {
            $logger->child(new Text($e->getMessage()))->updateStatus(Log::FAILURE);
            $logger->updateStatus(Log::FAILURE);

            throw $e;
        }

        return $image;
    }

    /**
     * @param Image $image
     *
     * @return string
     */
    private function getImageName(Image $image)
    {
        return $image->getName().':'.$image->getTag();
    }
}
