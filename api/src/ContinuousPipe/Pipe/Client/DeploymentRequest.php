<?php

namespace ContinuousPipe\Pipe\Client;

use ContinuousPipe\Pipe\Client\DeploymentRequest\Notification;
use ContinuousPipe\Pipe\Client\DeploymentRequest\Specification;
use ContinuousPipe\Pipe\Client\DeploymentRequest\Target;
use JMS\Serializer\Annotation as JMS;

class DeploymentRequest
{
    /**
     * @JMS\Type("ContinuousPipe\Pipe\Client\DeploymentRequest\Target")
     *
     * @var Target
     */
    private $target;

    /**
     * @JMS\Type("ContinuousPipe\Pipe\Client\DeploymentRequest\Specification")
     *
     * @var Specification
     */
    private $specification;

    /**
     * @JMS\Type("ContinuousPipe\Pipe\Client\DeploymentRequest\Notification")
     *
     * @var Notification
     */
    private $notification;

    /**
     * @param Target        $target
     * @param Specification $specification
     * @param Notification  $notification
     */
    public function __construct(Target $target, Specification $specification, Notification $notification)
    {
        $this->target = $target;
        $this->specification = $specification;
        $this->notification = $notification;
    }

    /**
     * @return Target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return Specification
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    /**
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }
}
