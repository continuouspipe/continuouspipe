<?php

namespace ContinuousPipe\Builder\Tests\Docker;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Docker\Client;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Logger;

class CallbackDockerClient implements Client
{
    /**
     * @var callable|null
     */
    private $buildCallback;

    /**
     * @var callable|null
     */
    private $pushCallback;

    /**
     * {@inheritdoc}
     */
    public function build(Archive $archive, BuildRequest $request, Logger $logger)
    {
        if (null === $this->buildCallback) {
            $this->buildCallback = self::getBuildSuccessCallback();
        }

        $callback = $this->buildCallback;

        return $callback($archive, $request, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function push(Image $image, RegistryCredentials $credentials, Logger $logger)
    {
        if (null === $this->pushCallback) {
            $this->pushCallback = self::getPushSuccessCallback();
        }

        $callback = $this->pushCallback;

        return $callback($image, $credentials, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function runAndCommit(Image $image, Logger $logger, $command)
    {
        return $image;
    }

    /**
     * @return callable
     */
    public static function getPushSuccessCallback()
    {
        return function (Image $image, RegistryCredentials $credentials, Logger $logger) {
            return;
        };
    }

    /**
     * @return callable
     */
    public static function getBuildSuccessCallback()
    {
        return function (Archive $archive, BuildRequest $request, Logger $logger) {
            return $request->getImage();
        };
    }

    /**
     * @param callable|null $buildCallback
     */
    public function setBuildCallback($buildCallback)
    {
        $this->buildCallback = $buildCallback;
    }

    /**
     * @param callable|null $pushCallback
     */
    public function setPushCallback($pushCallback)
    {
        $this->pushCallback = $pushCallback;
    }

    /**
     * @return callable|null
     */
    public function getBuildCallback()
    {
        return $this->buildCallback;
    }

    /**
     * @return callable|null
     */
    public function getPushCallback()
    {
        return $this->pushCallback;
    }
}
