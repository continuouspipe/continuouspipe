<?php

namespace ContinuousPipe\Builder\Aggregate\GoogleContainerBuilder\Command;

use ContinuousPipe\Builder\GoogleContainerBuilder\GoogleContainerBuild;
use ContinuousPipe\Message\Message;
use JMS\Serializer\Annotation as JMS;

class FetchGCBuildStatus implements Message
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $buildIdentifier;

    /**
     * @JMS\Type("ContinuousPipe\Builder\GoogleContainerBuilder\GoogleContainerBuild")
     *
     * @var GoogleContainerBuild
     */
    private $googleContainerBuild;

    /**
     * @param string $buildIdentifier
     * @param GoogleContainerBuild $googleContainerBuild
     */
    public function __construct(string $buildIdentifier, GoogleContainerBuild $googleContainerBuild)
    {
        $this->buildIdentifier = $buildIdentifier;
        $this->googleContainerBuild = $googleContainerBuild;
    }

    public function getBuildIdentifier() : string
    {
        return $this->buildIdentifier;
    }

    public function getGoogleContainerBuild() : GoogleContainerBuild
    {
        return $this->googleContainerBuild;
    }
}
