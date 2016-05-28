<?php

namespace ContinuousPipe\Builder\Request;

use ContinuousPipe\Builder\Context;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Logging;
use ContinuousPipe\Builder\Notification;
use ContinuousPipe\Builder\Repository;
use Ramsey\Uuid\Uuid;

class BuildRequest
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Image
     */
    private $image;

    /**
     * @var Notification
     */
    private $notification;

    /**
     * @var Logging
     */
    private $logging;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var array
     */
    private $environment;

    /**
     * @var Uuid
     */
    private $credentialsBucket;

    /**
     * @param Repository   $repository
     * @param Image        $image
     * @param Context      $context
     * @param Notification $notification
     * @param Logging      $logging
     * @param array        $environment
     * @param Uuid         $credentialsBucket
     */
    public function __construct(Repository $repository, Image $image, Context $context = null, Notification $notification = null, Logging $logging = null, array $environment = [], Uuid $credentialsBucket = null)
    {
        $this->repository = $repository;
        $this->image = $image;
        $this->notification = $notification;
        $this->logging = $logging;
        $this->context = $context;
        $this->environment = $environment;
        $this->credentialsBucket = $credentialsBucket;
    }

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @return Logging
     */
    public function getLogging()
    {
        return $this->logging;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return array
     */
    public function getEnvironment()
    {
        return $this->environment ?: [];
    }

    /**
     * @return Uuid
     */
    public function getCredentialsBucket()
    {
        if (is_string($this->credentialsBucket)) {
            $this->credentialsBucket = Uuid::fromString($this->credentialsBucket);
        }

        return $this->credentialsBucket;
    }
}
