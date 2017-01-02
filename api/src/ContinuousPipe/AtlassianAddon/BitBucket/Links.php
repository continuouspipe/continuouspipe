<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

class Links
{
    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Link")
     *
     * @var Link
     */
    private $self;

    /**
     * @return Link
     */
    public function getSelf(): Link
    {
        return $this->self;
    }
}
