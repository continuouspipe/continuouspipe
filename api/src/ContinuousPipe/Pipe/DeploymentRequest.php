<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Pipe\DeploymentRequest\Notification;
use ContinuousPipe\Pipe\DeploymentRequest\Specification;
use ContinuousPipe\Pipe\DeploymentRequest\Target;

class DeploymentRequest
{
    /**
     * @var Target
     */
    private $target;

    /**
     * @var Specification
     */
    private $specification;

    /**
     * @var Notification
     */
    private $notification;

    /**
     * @param Target        $target
     * @param Specification $specification
     * @param Notification  $notification
     */
    public function __construct(Target $target, Specification $specification, Notification $notification = null)
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
