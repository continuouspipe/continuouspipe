<?php

namespace AppBundle\Request\Managed;

use ContinuousPipe\Model\Component\ResourcesRequest;
use JMS\Serializer\Annotation as JMS;

class UsedResourcesRequest
{
    /**
     * @JMS\Type("AppBundle\Request\Managed\UsedResourcesNamespace")
     *
     * @var UsedResourcesNamespace
     */
    private $namespace;

    /**
     * @JMS\Type("ContinuousPipe\Model\Component\ResourcesRequest")
     *
     * @var ResourcesRequest
     */
    private $limits;

    /**
     * @JMS\Type("ContinuousPipe\Model\Component\ResourcesRequest")
     *
     * @var ResourcesRequest
     */
    private $requests;

    /**
     * @return UsedResourcesNamespace|null
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return ResourcesRequest|null
     */
    public function getLimits()
    {
        return $this->limits;
    }

    /**
     * @return ResourcesRequest|null
     */
    public function getRequests()
    {
        return $this->requests;
    }
}
