<?php

namespace ContinuousPipe\River\ClusterPolicies\Resources;

use ContinuousPipe\Model\Component\ResourcesRequest;

class AggregateResourcesRequest
{
    /**
     * @var null|int
     */
    private $cpu = null;

    /**
     * @var null|int
     */
    private $memory = null;

    public function add(ResourcesRequest $resourcesRequest)
    {
        if (null !== ($cpu = $resourcesRequest->getCpu())) {
            $this->cpu += ResourceConverter::resourceToNumber($cpu);
        }

        if (null !== ($memory = $resourcesRequest->getMemory())) {
            $this->memory += ResourceConverter::resourceToNumber($memory);
        }
    }

    public function toResourcesRequest() : ResourcesRequest
    {
        return new ResourcesRequest(
            (null === $this->cpu) ? null : ResourceConverter::cpuToString($this->cpu),
            (null === $this->memory) ? null : ResourceConverter::memoryToString($this->memory)
        );
    }
}
