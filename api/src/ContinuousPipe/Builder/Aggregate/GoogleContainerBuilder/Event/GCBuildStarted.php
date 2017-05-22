<?php

namespace ContinuousPipe\Builder\Aggregate\GoogleContainerBuilder\Event;

use ContinuousPipe\Builder\Aggregate\Event\BuildEvent;
use ContinuousPipe\Builder\GoogleContainerBuilder\GoogleContainerBuild;
use JMS\Serializer\Annotation as JMS;

class GCBuildStarted extends BuildEvent
{
    /**
     * @JMS\Type("ContinuousPipe\Builder\GoogleContainerBuilder\GoogleContainerBuild")
     *
     * @var GoogleContainerBuild
     */
    private $build;

    /**
     * @param string $buildIdentifier
     * @param GoogleContainerBuild $build
     */
    public function __construct(string $buildIdentifier, GoogleContainerBuild $build)
    {
        parent::__construct($buildIdentifier);

        $this->build = $build;
    }

    /**
     * @return GoogleContainerBuild
     */
    public function getBuild(): GoogleContainerBuild
    {
        return $this->build;
    }
}
