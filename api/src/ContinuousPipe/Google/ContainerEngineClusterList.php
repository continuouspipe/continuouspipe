<?php

namespace ContinuousPipe\Google;

use JMS\Serializer\Annotation as JMS;

final class ContainerEngineClusterList
{
    /**
     * @JMS\Type("array<ContinuousPipe\Google\ContainerEngineCluster>")
     * @JMS\SerializedName("clusters")
     *
     * @var ContainerEngineCluster[]
     */
    private $clusters = [];

    /**
     * @return ContainerEngineCluster[]
     */
    public function getClusters(): array
    {
        return $this->clusters;
    }
}
