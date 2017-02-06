<?php

namespace ContinuousPipe\Builder\Request;

use ContinuousPipe\Builder\Context;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Logging;
use ContinuousPipe\Builder\Notification;
use ContinuousPipe\Builder\Repository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class BuildRequest
{
    /**
     * @var BuildRequestStep[]
     */
    private $steps;

    /**
     * @var Notification
     */
    private $notification;

    /**
     * @var Logging
     */
    private $logging;

    /**
     * @var UuidInterface
     */
    private $credentialsBucket;

    /**
     * @param BuildRequestStep[] $steps
     * @param Notification       $notification
     * @param Logging            $logging
     * @param UuidInterface      $credentialsBucket
     */
    public function __construct(array $steps, Notification $notification, Logging $logging, UuidInterface $credentialsBucket)
    {
        $this->steps = $steps;
        $this->notification = $notification;
        $this->logging = $logging;
        $this->credentialsBucket = $credentialsBucket;
    }

    /**
     * @return BuildRequestStep[]
     */
    public function getSteps(): array
    {
        return $this->steps ?: [];
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
     * @return UuidInterface
     */
    public function getCredentialsBucket()
    {
        if (is_string($this->credentialsBucket)) {
            $this->credentialsBucket = Uuid::fromString($this->credentialsBucket);
        }

        return $this->credentialsBucket;
    }
}
