<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\AtlassianAddon\BitBucket\Actor;
use ContinuousPipe\AtlassianAddon\BitBucket\Team;
use JMS\Serializer\Annotation as JMS;

class BitBucketAccount
{
    /**
     * @JMS\Groups({"Default"})
     * @JMS\Type("string")
     *
     * @var string
     */
    private $uuid;

    /**
     * @JMS\Groups({"Default"})
     * @JMS\Type("string")
     *
     * @var string
     */
    private $username;

    /**
     * @JMS\Groups({"Default"})
     * @JMS\Type("string")
     *
     * @var string
     */
    private $type;

    /**
     * @JMS\Groups({"Default"})
     * @JMS\Type("string")
     *
     * @var string|null
     */
    private $displayName;

    public function __construct(string $uuid, string $username, string $type, string $displayName = null)
    {
        $this->uuid = $uuid;
        $this->username = $username;
        $this->type = $type;
        $this->displayName = $displayName;
    }

    public static function fromActor(Actor $actor) : BitBucketAccount
    {
        return new self(
            $actor->getUuid(),
            $actor->getUsername(),
            $actor instanceof Team ? 'team' : 'user',
            $actor->getDisplayName()
        );
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return null|string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }
}
