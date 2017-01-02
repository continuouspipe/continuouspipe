<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

class Project
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $key;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $uuid;
}
