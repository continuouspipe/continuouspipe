<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\Model\Component;
use JMS\Serializer\Annotation as JMS;

class DeployTaskConfiguration
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $clusterIdentifier;

    /**
     * @JMS\Type("array<string, ContinuousPipe\Model\Component>")
     *
     * @var Component[]
     */
    private $services;

    /**
     * @param string      $clusterIdentifier
     * @param Component[] $services
     */
    public function __construct($clusterIdentifier, array $services)
    {
        $this->clusterIdentifier = $clusterIdentifier;
        $this->services = $services;
    }

    /**
     * @return string
     */
    public function getClusterIdentifier()
    {
        return $this->clusterIdentifier;
    }

    /**
     * @return \ContinuousPipe\Model\Component[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @return \ContinuousPipe\Model\Component[]
     */
    public function getComponents()
    {
        return $this->services;
    }
}
