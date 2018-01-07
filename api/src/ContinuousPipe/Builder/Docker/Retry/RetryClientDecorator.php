<?php

namespace ContinuousPipe\Builder\Docker\Retry;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Docker\BuildContext;
use ContinuousPipe\Builder\Docker\DockerFacade;
use ContinuousPipe\Builder\Docker\DockerException;
use ContinuousPipe\Builder\Docker\Exception\DaemonException;
use ContinuousPipe\Builder\Docker\Exception\PushAlreadyInProgress;
use ContinuousPipe\Builder\Docker\PushContext;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Psr\Log\LoggerInterface;
use Tolerance\Waiter\SleepWaiter;

class RetryClientDecorator implements DockerFacade
{
    /**
     * @var DockerFacade
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var int
     */
    private $maxRetries;

    /**
     * @var int
     */
    private $retryInterval;

    /**
     * @param DockerFacade $client
     * @param LoggerInterface $logger
     * @param LoggerFactory $loggerFactory
     * @param int $maxRetries
     * @param int $retryInterval
     */
    public function __construct(DockerFacade $client, LoggerInterface $logger, LoggerFactory $loggerFactory, $maxRetries = 10, $retryInterval = 30)
    {
        $this->client = $client;
        $this->loggerFactory = $loggerFactory;
        $this->logger = $logger;
        $this->maxRetries = $maxRetries;
        $this->retryInterval = $retryInterval;
    }

    /**
     * {@inheritdoc}
     */
    public function build(BuildContext $context, Archive $archive) : Image
    {
        return $this->client->build($context, $archive);
    }

    /**
     * {@inheritdoc}
     */
    public function push(PushContext $context, Image $image)
    {
        $remainingAttempts = $this->maxRetries;

        do {
            try {
                return $this->client->push($context, $image);
            } catch (DockerException $e) {
                if (!$this->shouldRetryBasedOnException($e)) {
                    throw $e;
                }
            }

            $this->logger->warning('Detected a Docker error while pushing an image: {error}', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            $this->loggerFactory->fromId($context->getLogStreamIdentifier())->child(new Text(sprintf(
                "\n".'Detected a Docker error, retrying in %d seconds: %s'."\n",
                $this->retryInterval,
                $e->getMessage()
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
