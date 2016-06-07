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
use Tolerance\Waiter\SleepWaiter;

class RetryClientDecorator implements Client
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    private $maxRetries;

    /**
     * @var int
     */
    private $retryInterval;

    /**
     * @param Client $client
     * @param int    $maxRetries
     * @param int    $retryInterval
     */
    public function __construct(Client $client, $maxRetries = 10, $retryInterval = 30)
    {
        $this->client = $client;
        $this->maxRetries = $maxRetries;
        $this->retryInterval = $retryInterval;
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
        $remainingAttempts = $this->maxRetries;

        do {
            try {
                return $this->client->push($image, $credentials, $logger);
            } catch (DockerException $e) {
                if (!$this->shouldRetryBasedOnException($e)) {
                    throw $e;
                }
            }

            $logger->child(new Text(sprintf(
                "\n".'Detected a Docker error, retrying in %s seconds'."\n",
                $this->retryInterval
            )));

            (new SleepWaiter())->wait($this->retryInterval);
        } while ($remainingAttempts-- > 0);

        throw new DockerException(
            sprintf('Failed even after %d retries: %s', $this->maxRetries, $e->getMessage()),
            $e->getCode(),
            $e
        );
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
