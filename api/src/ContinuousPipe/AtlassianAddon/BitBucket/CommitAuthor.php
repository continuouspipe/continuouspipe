<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

class CommitAuthor
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $raw;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\User")
     *
     * @var User
     */
    private $user;

    /**
     * @return string
     */
    public function getRaw(): string
    {
        return $this->raw;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
