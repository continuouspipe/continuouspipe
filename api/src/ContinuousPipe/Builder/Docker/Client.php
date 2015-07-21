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

    public function __construct(Docker $docker)
    {
        $this->docker = $docker;
    }

    public function build(Archive $archive, Image $image, Logger $logger)
    {
        $imageName = $image->getName().':'.$image->getTag();
        $this->docker->build($archive, $imageName, $this->getOutputCallback($logger));
    }

    public function push(Image $image, RegistryCredentials $credentials, Logger $logger)
    {
        $this->docker->getImageManager()->push(
            $image->getName(), $image->getTag(),
            $credentials->getAuthenticationString(),
            $this->getOutputCallback($logger)
        );
    }

    private function getOutputCallback(Logger $logger)
    {
        return function ($output) use ($logger) {
            if (is_array($output) && array_key_exists('error', $output)) {
                $log = Log::error($output['error']);
            } elseif (is_array($output) && array_key_exists('stream', $output)) {
                $log = Log::output($output['stream']);
            } elseif (is_array($output) && array_key_exists('status', $output)) {
                $log = Log::output($output['status']);
            } elseif (is_string($output)) {
                $log = Log::output($output);
            } else {
                $log = Log::error(print_r($output, true));
            }

            if (!empty($log)) {
                $logger->log($log);
            }
        };
    }
}
