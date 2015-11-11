<?php

namespace ContinuousPipe\Builder\Docker\Retry;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Docker\Client;
use ContinuousPipe\Builder\Docker\DockerException;
use ContinuousPipe\Builder\Docker\Exception\DaemonException;
use ContinuousPipe\Builder\Docker\Exception\PushAlreadyInProgress;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Logger;
use LogStream\Node\Text;

class RetryClientDecorator implements Client
{
    /**
     * Max retry count.
     */
    const MAX_RETRIES = 3;

    /**
     * Delay between retries, in seconds.
     *
     * @var int
     */
    const DELAY_BETWEEN_RETRIES = 5;
    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function runAndCommit(Image $image, Logger $logger, $command)
    {
        return $this->client->runAndCommit($image, $logger, $command);
    }

    /**
     * {@inheritdoc}
     */
    public function build(Archive $archive, BuildRequest $request, Logger $logger)
    {
        return $this->client->build($archive, $request, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function push(Image $image, RegistryCredentials $credentials, Logger $logger)
    {
        $attempts = 0;

        do {
            try {
                return $this->client->push($image, $credentials, $logger);
            } catch (DockerException $e) {
                if (!$this->shouldRetryBasedOnException($e)) {
                    throw $e;
                }
            }

            $retryIn = self::DELAY_BETWEEN_RETRIES * ($attempts + 1);
            $logger->append(new Text(sprintf(
                'Detected infrastructure error, retrying in %s seconds',
                $retryIn
            )));

            sleep($retryIn);
        } while (++$attempts <= self::MAX_RETRIES);

        throw new DockerException(sprintf('Failed even after retries: %s', $e->getMessage()), $e->getCode(), $e);
    }

    /**
     * @param DockerException $e
     *
     * @return bool
     */
    private function shouldRetryBasedOnException(DockerException $e)
    {
        return $e instanceof PushAlreadyInProgress
            || $e instanceof DaemonException;
    }
}
