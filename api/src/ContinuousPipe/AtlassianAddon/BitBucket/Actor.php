<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\Discriminator(field="type", map = {
 *    "user": "ContinuousPipe\AtlassianAddon\BitBucket\User",
 *    "team": "ContinuousPipe\AtlassianAddon\BitBucket\Team"
 * })
 */
abstract class Actor
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $username;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("display_name")
     *
     * @var string
     */
    private $displayName;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $uuid;

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string|null
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }
}
