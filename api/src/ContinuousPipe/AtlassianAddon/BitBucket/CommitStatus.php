<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

class CommitStatus
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
    private $description;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $state;

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
    private $url;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $type;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $createdOn;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $updatedOn;
}
