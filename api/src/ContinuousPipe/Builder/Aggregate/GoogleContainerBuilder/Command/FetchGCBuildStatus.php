<?php

namespace ContinuousPipe\Builder\Aggregate\GoogleContainerBuilder\Command;

use ContinuousPipe\Builder\GoogleContainerBuilder\GoogleContainerBuild;
use ContinuousPipe\Message\Delay\DelayedMessage;
use JMS\Serializer\Annotation as JMS;

class FetchGCBuildStatus implements DelayedMessage
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
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $runAt;

    /**
     * @param string $buildIdentifier
     * @param GoogleContainerBuild $googleContainerBuild
     * @param \DateTime $runAt
     */
    public function __construct(
        string $buildIdentifier,
        GoogleContainerBuild $googleContainerBuild,
        \DateTime $runAt = null
    ) {
        $this->buildIdentifier = $buildIdentifier;
        $this->googleContainerBuild = $googleContainerBuild;
        $this->runAt = $runAt;
    }

    public function getBuildIdentifier() : string
    {
        return $this->buildIdentifier;
    }

    public function getGoogleContainerBuild() : GoogleContainerBuild
    {
        return $this->googleContainerBuild;
    }

    /**
     * Date/time at which the message have to be run.
     *
     * @return \DateTimeInterface
     */
    public function runAt() : \DateTimeInterface
    {
        return $this->runAt;
    }
}