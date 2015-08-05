<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Image;
use Docker\Docker;
use Docker\Exception\UnexpectedStatusCodeException;
use GuzzleHttp\Exception\RequestException;
use LogStream\Logger;
use LogStream\Node\Text;

class HttpClient implements Client
{
    /**
     * @var Docker
     */
    private $docker;

    /**
     * @param Docker $docker
     */
    public function __construct(Docker $docker)
    {
        $this->docker = $docker;
    }

    /**
     * {@inheritdoc}
     */
    public function build(Archive $archive, Image $image, Logger $logger)
    {
        $imageName = $image->getName().':'.$image->getTag();
        $this->docker->build($archive, $imageName, $this->getOutputCallback($logger));
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
     * Get the client stream callback.
     *
     * @param Logger $logger
     *
     * @return callable
     */
    private function getOutputCallback(Logger $logger)
    {
        return function ($output) use ($logger) {
            if (is_array($output) && array_key_exists('error', $output)) {
                throw new DockerException($output['error']);
            } elseif (is_array($output) && array_key_exists('stream', $output)) {
                $rawOutput = $output['stream'];
            } elseif (is_array($output) && array_key_exists('status', $output)) {
                $rawOutput = $output['status'];
            } elseif (is_string($output)) {
                $rawOutput = $output;
            } else {
                throw new DockerException(print_r($output, true));
            }

            if (!empty($rawOutput)) {
                $logger->append(new Text($rawOutput));
            }
        };
    }
}
