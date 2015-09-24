<?php

namespace ContinuousPipe\Pipe\Client\DeploymentRequest;

use ContinuousPipe\Model\Component;
use JMS\Serializer\Annotation as JMS;

class Specification
{
    /**
     * @JMS\Type("array<ContinuousPipe\Model\Component>")
     * @JMS\SerializedName("components")
     *
     * @var Component[]
     */
    private $components;

    /**
     * @param Component[] $components
     */
    public function __construct(array $components)
    {
        $this->components = $components;
    }

    /**
     * @return \ContinuousPipe\Model\Component[]
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * @return string
     */
    public function getDockerComposeContents()
    {
        return $this->dockerComposeContents;
    }
}
