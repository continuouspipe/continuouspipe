<?php

namespace ContinuousPipe\Builder\Aggregate\GoogleContainerBuilder\Event;

use ContinuousPipe\Builder\Aggregate\Event\BuildEvent;
use ContinuousPipe\Builder\GoogleContainerBuilder\GoogleContainerBuildStatus;
use JMS\Serializer\Annotation as JMS;

class GCBuildFinished extends BuildEvent
{
    /**
     * @JMS\Type("ContinuousPipe\Builder\GoogleContainerBuilder\GoogleContainerBuildStatus")
     *
     * @var GoogleContainerBuildStatus
     */
    private $status;

    /**
     * @param string $buildIdentifier
     * @param GoogleContainerBuildStatus $status
     */
    public function __construct(string $buildIdentifier, GoogleContainerBuildStatus $status)
    {
        parent::__construct($buildIdentifier);

        $this->status = $status;
    }

    /**
     * @return GoogleContainerBuildStatus
     */
    public function getStatus(): GoogleContainerBuildStatus
    {
        return $this->status;
    }
}
