<?php

namespace ContinuousPipe\River\Managed\Resources;

use ContinuousPipe\Model\Component\ResourcesRequest;

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

    /**
     * @return ResourcesRequest|null
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * @return ResourcesRequest|null
     */
    public function getLimits()
    {
        return $this->limits;
    }
}
