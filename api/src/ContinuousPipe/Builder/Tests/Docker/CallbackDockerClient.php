<?php

namespace ContinuousPipe\Builder\Tests\Docker;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Docker\BuildContext;
use ContinuousPipe\Builder\Docker\DockerFacade;
use ContinuousPipe\Builder\Docker\PushContext;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Logger;

class CallbackDockerClient implements DockerFacade
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
    public function build(BuildContext $context, Archive $archive) : Image
    {
        if (null === $this->buildCallback) {
            $this->buildCallback = self::getBuildSuccessCallback();
        }

        $callback = $this->buildCallback;

        return $callback($context, $archive);
    }

    /**
     * {@inheritdoc}
     */
    public function push(PushContext $context, Image $image)
    {
        if (null === $this->pushCallback) {
            $this->pushCallback = self::getPushSuccessCallback();
        }

        $callback = $this->pushCallback;

        return $callback($context, $image);
    }

    /**
     * @return callable
     */
    public static function getPushSuccessCallback()
    {
        return function (PushContext $context, Image $image) {
            return;
        };
    }

    /**
     * @return callable
     */
    public static function getBuildSuccessCallback()
    {
        return function (BuildContext $context, Archive $archive) {
            return $context->getImage();
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
