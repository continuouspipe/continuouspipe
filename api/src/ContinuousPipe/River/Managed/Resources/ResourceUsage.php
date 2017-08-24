<?php

namespace ContinuousPipe\River\Managed\Resources;

class ResourceUsage
{
    /**
     * @var array|ClusterResourceUsage[]
     */
    private $clusterUsages;

    /**
     * @param ClusterResourceUsage[] $clusterUsages
     */
    public function __construct(array $clusterUsages)
    {
        $this->clusterUsages = $clusterUsages;
    }

    /**
     * @return array|ClusterResourceUsage[]
     */
    public function getClusterUsages()
    {
        return $this->clusterUsages;
    }
}
