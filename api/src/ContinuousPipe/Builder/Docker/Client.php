<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\LogStream\Log;
use ContinuousPipe\LogStream\Logger;
use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Image;
use Docker\Docker;

class Client
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
     * @param Archive $archive
     * @param Image   $image
     * @param Logger  $logger
     */
    public function build(Archive $archive, Image $image, Logger $logger)
    {
        $imageName = $image->getName().':'.$image->getTag();
        $this->docker->build($archive, $imageName, $this->getOutputCallback($logger));
    }

    /**
     * @param Image               $image
     * @param RegistryCredentials $credentials
     * @param Logger              $logger
     *
     * @throws DockerException
     * @throws \Docker\Exception\UnexpectedStatusCodeException
     */
    public function push(Image $image, RegistryCredentials $credentials, Logger $logger)
    {
        $this->docker->getImageManager()->push(
            $image->getName(), $image->getTag(),
            $credentials->getAuthenticationString(),
            $this->getOutputCallback($logger)
        );
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
                $log = Log::output($output['stream']);
            } elseif (is_array($output) && array_key_exists('status', $output)) {
                $log = Log::output($output['status']);
            } elseif (is_string($output)) {
                $log = Log::output($output);
            } else {
                throw new DockerException(print_r($output, true));
            }

            if (!empty($log)) {
                $logger->log($log);
            }
        };
    }
}
