<?php

namespace ContinuousPipe\River\Managed\Resources;

use ContinuousPipe\Model\Component\ResourcesRequest;
use ContinuousPipe\River\Managed\Resources\Calculation\ResourceConverter;

class ResourceUsage
{
    /**
     * @var ResourcesRequest|null
     */
    private $requests;

    /**
     * @var ResourcesRequest|null
     */
    private $limits;

    /**
     * @param ResourcesRequest|null $requests
     * @param ResourcesRequest|null $limits
     */
    public function __construct(ResourcesRequest $requests = null, ResourcesRequest $limits = null)
    {
        $this->requests = $requests;
        $this->limits = $limits;
    }

    public static function zero() : self
    {
        return new self(
            new ResourcesRequest(0, 0),
            new ResourcesRequest(0, 0)
        );
    }

    /**
     * Return the maximum resource usage.
     *
     * @param ResourceUsage $usage
     *
     * @return ResourceUsage
     */
    public function max(ResourceUsage $usage) : self
    {
        return new ResourceUsage(
            $this->maxResources($this->getRequests(), $usage->getRequests()),
            $this->maxResources($this->getLimits(), $usage->getLimits())
        );
    }

    /**
     * @return ResourcesRequest
     */
    public function getRequests()
    {
        return $this->requests ?: new ResourcesRequest();
    }

    /**
     * @return ResourcesRequest
     */
    public function getLimits()
    {
        return $this->limits ?: new ResourcesRequest();
    }

    /**
     * Return the maximum of the resource requests.
     *
     * @param ResourcesRequest $left
     * @param ResourcesRequest $right
     *
     * @return ResourcesRequest
     */
    private function maxResources(ResourcesRequest $left, ResourcesRequest $right) : ResourcesRequest
    {
        return new ResourcesRequest(
            ResourceConverter::cpuToString(
                max(
                    ResourceConverter::resourceToNumber($left->getCpu()),
                    ResourceConverter::resourceToNumber($right->getCpu())
                )
            ),
            ResourceConverter::memoryToString(
                max(
                    ResourceConverter::resourceToNumber($left->getMemory()),
                    ResourceConverter::resourceToNumber($right->getMemory())
                )
            )
        );
    }
}
