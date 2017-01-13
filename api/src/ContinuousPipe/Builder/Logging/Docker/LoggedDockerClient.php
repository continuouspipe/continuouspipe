<?php

namespace ContinuousPipe\Builder\Logging\Docker;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Docker\BuildContext;
use ContinuousPipe\Builder\Docker\DockerContext;
use ContinuousPipe\Builder\Docker\DockerFacade;
use ContinuousPipe\Builder\Docker\DockerException;
use ContinuousPipe\Builder\Docker\PushContext;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Log;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Raw;
use LogStream\Node\Text;

class LoggedDockerClient implements DockerFacade
{
    /**
     * @var DockerFacade
     */
    private $client;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param DockerFacade        $client
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(DockerFacade $client, LoggerFactory $loggerFactory)
    {
        $this->client = $client;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function build(BuildContext $context, Archive $archive) : Image
    {
        $title = $context->getImage() === null
            ? 'TODO identify the message'
            : sprintf('Building Docker image <code>%s</code>', $this->getImageName($context->getImage()));

        return $this->wraps(
            $title,
            $context,
            function ($context) use ($archive) {
                return $this->client->build($context, $archive);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function push(PushContext $context, Image $image)
    {
        return $this->wraps(
            sprintf('Pushing Docker image <code>%s</code>', $this->getImageName($image)),
            $context,
            function ($context) use ($image) {
                return $this->client->push($context, $image);
            }
        );
    }

    /**
     * @param string $title
     * @param DockerContext $context
     * @param callable $callable
     *
     * @throws DockerException
     *
     * @return mixed
     */
    private function wraps(string $title, DockerContext $context, callable $callable)
    {
        $logger = $this->loggerFactory->fromId($context->getLogStreamIdentifier())->child(new Text($title))->updateStatus(Log::RUNNING);

        $context = $context->withLogStreamIdentifier(
            $logger->child(new Raw())->getLog()->getId()
        );

        try {
            $image = $callable($context);

            $logger->updateStatus(Log::SUCCESS);
        } catch (DockerException $e) {
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
