<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket\WebHook;

use ContinuousPipe\AtlassianAddon\BitBucket\Reference;
use JMS\Serializer\Annotation as JMS;

class Change
{
    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Reference")
     *
     * @var Reference
     */
    private $new;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Reference")
     *
     * @var Reference
     */
    private $old;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $closed;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $created;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $truncated;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $forced;

    /**
     * @return Reference|null
     */
    public function getNew()
    {
        return $this->new;
    }

    /**
     * @return Reference|null
     */
    public function getOld()
    {
        return $this->old;
    }

    /**
     * @return bool
     */
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * @return bool
     */
    public function isCreated(): bool
    {
        return $this->created;
    }

    /**
     * @return bool
     */
    public function isTruncated(): bool
    {
        return $this->truncated;
    }

    /**
     * @return bool
     */
    public function isForced(): bool
    {
        return $this->forced;
    }
}
