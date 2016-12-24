<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

class BitBucketAccount
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $type;

    /**
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
