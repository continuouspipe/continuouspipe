<?php

namespace ContinuousPipe\Builder\Aggregate\Command;

use ContinuousPipe\Builder\GoogleContainerBuilder\GoogleContainerBuildStatus;
use ContinuousPipe\Message\Message;
use JMS\Serializer\Annotation as JMS;

class CompleteBuild implements Message
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $buildIdentifier;

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
        $this->buildIdentifier = $buildIdentifier;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getBuildIdentifier(): string
    {
        return $this->buildIdentifier;
    }

    public function getStatus(): GoogleContainerBuildStatus
    {
        return $this->status;
    }
}
