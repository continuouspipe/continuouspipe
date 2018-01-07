<?php

namespace ContinuousPipe\Pipe\Client;

use ContinuousPipe\Pipe\Client\DeploymentRequest\Notification;
use ContinuousPipe\Pipe\Client\DeploymentRequest\Specification;
use ContinuousPipe\Pipe\Client\DeploymentRequest\Target;
use Ramsey\Uuid\UuidInterface;

/**
 * @deprecated Duplicate of the `ContinuousPipe\Pipe\DeploymentRequest` object, after merging pipe. Kept to be compatible
 *             with serialized tides.
 */
class DeploymentRequest extends \ContinuousPipe\Pipe\DeploymentRequest
{
    private $target;
    private $specification;
    private $notification;
    private $credentialsBucket;

    public function __construct(Target $target, Specification $specification, Notification $notification, UuidInterface $credentialsBucket)
    {
        parent::__construct($target, $specification, $credentialsBucket, $notification);
    }

    public function getTarget()
    {
        return $this->target ?? parent::getTarget();
    }

    public function getSpecification()
    {
        return $this->specification ?? parent::getSpecification();
    }

    public function getNotification()
    {
        return $this->notification ?? parent::getNotification();
    }

    public function getCredentialsBucket()
    {
        return $this->credentialsBucket ?? parent::getCredentialsBucket();
    }
}
