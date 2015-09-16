<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Request\BuildRequest;
use Docker\Container;
use Docker\Docker;
use Docker\Exception\UnexpectedStatusCodeException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Stream\Stream;
use LogStream\Logger;
use LogStream\Node\Text;

class HttpClient implements Client
{
    /**
     * @var Docker
     */
    private $docker;

    /**
     * @var DockerfileResolver
     */
    private $dockerfileResolver;

    /**
     * @param Docker             $docker
     * @param DockerfileResolver $dockerfileResolver
     */
    public function __construct(Docker $docker, DockerfileResolver $dockerfileResolver)
    {
        $this->docker = $docker;
        $this->dockerfileResolver = $dockerfileResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function build(Archive $archive, BuildRequest $request, Logger $logger)
    {
        return $this->doBuild($archive, $request, $this->getOutputCallback($logger));
    }

    /**
     * {@inheritdoc}
     */
    public function push(Image $image, RegistryCredentials $credentials, Logger $logger)
    {
        try {
            $this->docker->getImageManager()->push(
                $image->getName(), $image->getTag(),
                $credentials->getAuthenticationString(),
                $this->getOutputCallback($logger)
            );
        } catch (UnexpectedStatusCodeException $e) {
            throw new DockerException($e->getMessage(), $e->getCode(), $e);
        } catch (RequestException $e) {
            throw new DockerException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createContainer(Image $image)
    {
        $container = new Container([
            'Image' => $this->getImageName($image),
        ]);

        $this->docker->getContainerManager()->create($container);

        return $container;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Container $container, Logger $logger, $command)
    {
        $manager = $this->docker->getContainerManager();
        try {
            $execId = $manager->exec($container, [
                '/bin/sh', '-c', $command,
            ]);
        } catch (UnexpectedStatusCodeException $e) {
            throw new DockerException($e->getMessage(), $e->getCode(), $e);
        }

        try {
            $manager->execstart($execId, $this->getOutputCallback($logger));
        } catch (RequestException $e) {
            throw new DockerException($e->getMessage(), $e->getCode(), $e);
        }

        return $container;
    }

    /**
     * {@inheritdoc}
     */
    public function commit(Container $container, Image $image)
    {
        try {
            return $this->docker->commit($container, [
                'repo' => $image->getName(),
                'tag' => $image->getTag(),
            ]);
        } catch (UnexpectedStatusCodeException $e) {
            throw new DockerException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get the client stream callback.
     *
     * @param Logger $logger
     *
     * @return callable
     */
    private function getOutputCallback(Logger $logger)
    {
        return function ($output) use ($logger) {
            if (is_array($output)) {
                if (array_key_exists('error', $output)) {
                    if (!is_string($output)) {
                        $output = 'Stringified error: '.print_r($output, true);
                    }

                    throw new DockerException($output);
                } elseif (array_key_exists('stream', $output)) {
                    $output = $output['stream'];
                } elseif (array_key_exists('status', $output)) {
                    $output = $output['status'];
                }
            }

            if (null !== $output && !is_string($output)) {
                $output = 'Unknown ('.gettype($output).')';
            }

            if (!empty($output)) {
                $logger->append(new Text($output));
            }
        };
    }

    /**
     * @param Archive      $archive
     * @param BuildRequest $request
     * @param callable     $callback
     *
     * @return Image
     */
    private function doBuild(Archive $archive, BuildRequest $request, callable $callback)
    {
        $image = $request->getImage();

        $options = [
            'q' => (integer) false,
            't' => $this->getImageName($image),
            'nocache' => (integer) false,
            'rm' => (integer) false,
            'dockerfile' => $this->dockerfileResolver->getFilePath($request->getContext()),
        ];

        $content = $archive->isStreamed() ? new Stream($archive->read()) : $archive->read();

        $this->docker->getHttpClient()->post(['/build{?data*}', ['data' => $options]], [
            'headers' => ['Content-Type' => 'application/tar'],
            'body' => $content,
            'stream' => true,
            'callback' => $callback,
            'wait' => true,
        ]);

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
