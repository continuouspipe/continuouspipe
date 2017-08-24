<?php

namespace ContinuousPipe\River\Managed\Resources;

use ContinuousPipe\Model\Component\Resources;

class ClusterResourceUsage
{
    /**
     * @var string
     */
    private $clusterIdentifier;

    /**
     * @var Resources
     */
    private $resources;

    /**
     * @param string $clusterIdentifier
     * @param Resources $resources
     */
    public function __construct(string $clusterIdentifier, Resources $resources)
    {
        $this->clusterIdentifier = $clusterIdentifier;
        $this->resources = $resources;
    }

    /**
     * @return string
     */
    public function getClusterIdentifier(): string
    {
        return $this->clusterIdentifier;
    }

    /**
     * @return Resources
     */
    public function getResources(): Resources
    {
        return $this->resources;
    }
}
