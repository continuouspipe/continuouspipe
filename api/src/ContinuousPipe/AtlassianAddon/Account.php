<?php

namespace ContinuousPipe\AtlassianAddon;

use JMS\Serializer\Annotation as JMS;

class Account
{
    const TYPE_USER = 'user';
    const TYPE_TEAM = 'team';

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $uuid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $username;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $type;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
