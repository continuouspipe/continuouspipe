<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

class Link
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $href;

    /**
     * @return string
     */
    public function getHref(): string
    {
        return $this->href;
    }
}
